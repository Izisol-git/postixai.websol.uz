<?php

namespace App\Console\Commands;

use App\Models\UserPhone;
use danog\MadelineProto\API;
use App\Models\InfinityMessage;
use App\Models\TelegramMessage;
use danog\MadelineProto\Logger;
use Illuminate\Console\Command;
use danog\MadelineProto\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use danog\MadelineProto\Settings\Logger as LoggerSettings;

class SendUnifiedTelegram extends Command
{
    protected $signature = 'telegram:send-unified';
    protected $description = 'Eng mukammal daemon: 100+ account, hech qanday crash, minimal log';

    private static array $instances = [];

    public function handle()
    {
        $this->info('Universal Telegram Daemon ishga tushdi');

        while (true) {
            $loopStart = microtime(true);

            $pending  = $this->getPendingMessages();
            $infinity = $this->getInfinityMessages();

            if ($pending->isEmpty() && $infinity->isEmpty()) {
                usleep(600_000); // 0.6 sek — xotira va CPU tejaydi
                continue;
            }

            $grouped = $pending->concat($infinity)->groupBy('user_phone_id');

            foreach ($grouped as $userPhoneId => $messages) {
                $this->processAccount($userPhoneId, $messages);
            }

            // Dinamik sleep: qancha ko‘p ishlagan bo‘lsa, shuncha kam uxlash
            $elapsed = microtime(true) - $loopStart;
            $sleepUs = max(100_000, 1_000_000 - (int)($elapsed * 1_000_000));
            usleep($sleepUs);
        }
    }

    private function getPendingMessages()
    {
        return TelegramMessage::query()
            ->where('telegram_messages.status', 'pending')
            ->where('telegram_messages.send_at', '<=', now())
            ->join('message_groups', 'telegram_messages.message_group_id', '=', 'message_groups.id')
            ->select('telegram_messages.id', 'telegram_messages.peer', 'telegram_messages.message_text', 'message_groups.user_phone_id')
            ->addSelect([DB::raw("'normal' as type")])
            ->get();
    }

    private function getInfinityMessages()
    {
        return InfinityMessage::query()
            ->where('infinity_messages.status', 'pending')
            ->where('infinity_messages.starts_at', '<=', now())
            ->where(fn($q) => $q->whereNull('infinity_messages.last_sent')
                ->orWhereRaw('TIMESTAMPDIFF(SECOND, infinity_messages.last_sent, NOW()) >= infinity_messages.interval'))
            ->join('message_groups', 'infinity_messages.message_group_id', '=', 'message_groups.id')
            ->select(
                'infinity_messages.id',
                'infinity_messages.peer',
                'infinity_messages.message_text',
                'infinity_messages.interval',
                'infinity_messages.sent_count',
                'message_groups.user_phone_id'
            )
            ->addSelect([DB::raw("'infinity' as type")])
            ->get();
    }

    private function processAccount(int $userPhoneId, $messages)
    {
        $userPhone = UserPhone::find($userPhoneId);

        if (!$userPhone || !file_exists($userPhone->session_path)) {
            Log::warning("Session yo'q: user_phone_id={$userPhoneId}");
            $this->failAll($messages);
            return;
        }

        $sessionKey = $userPhone->session_path;

        // Sessiya ochilmagan bo'lsa — ochamiz
        if (!isset(self::$instances[$sessionKey])) {
            try {
                $Madeline = $this->createApi($sessionKey);
                $Madeline->start();
                self::$instances[$sessionKey] = $Madeline;

                Log::info("Sessiya ochildi: {$userPhone->phone} (id: {$userPhoneId})");
            } catch (\Throwable $e) {
                Log::error("Sessiya ochilmadi: {$userPhone->phone} | " . $e->getMessage());
                
                // $userPhone->update(['session_path' => null]);
                unset(self::$instances[$sessionKey]);

                unset(self::$instances[$sessionKey]);
                $this->failAll($messages);
                return;
            }
        }

        $Madeline = self::$instances[$sessionKey];

        try {
            foreach ($messages as $row) {
                $this->sendSingle($Madeline, $row);
            }
        } catch (\Throwable $e) {
            Log::critical("Yuborishda fatal xato: {$userPhone->phone} | " . $e->getMessage());

            // Sessiya buzilgan → keyingi loopda qayta ochiladi
            unset(self::$instances[$sessionKey]);
        }
    }

    private function createApi(string $sessionPath): API
    {
        $settings = new Settings;
        $settings->getAppInfo()
            ->setApiId((int) env('TELEGRAM_API_ID'))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        // LOG O‘CHIRILDI — xotira tejaydi
        $settings->setLogger((new LoggerSettings)->setType(Logger::FILE_LOGGER));

        // Xotira va tezlik optimallashtirish
        // $settings->getSerialization()->setSerializationInterval(600); // 10 daqiqada saqlaydi

        return new API($sessionPath, $settings);
    }

    private function sendSingle(API $Madeline, $row): void
    {
        try {
            $Madeline->messages->sendMessage([
                'peer' => $row->peer,
                'message' => $row->message_text,
                'no_webpage' => true,
            ]);

            if ($row->type === 'infinity') {
                InfinityMessage::where('id', $row->id)->update([
                    'last_sent' => now(),
                    'sent_count' => DB::raw('sent_count + 1'),
                    'attempts' => 0,
                ]);
            } else {
                TelegramMessage::where('id', $row->id)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'attempts' => 0,
                ]);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $peer  = $row->peer;
            $type  = $row->type;

            // Faqat eng muhim xatolar logga tushadi
            if (str_contains($error, 'FLOOD_WAIT_') || str_contains($error, 'SLOWMODE_WAIT_')) {
                $this->handleFlood($row, $error);
            } elseif (str_contains($error, 'CHAT_WRITE_FORBIDDEN') || str_contains($error, 'USER_BANNED')) {
                $this->handleBan($row);
            } else {
                Log::error("Yuborish xatosi [{$type}]: {$peer} | {$error}");
            }
        }
    }

    private function handleFlood($row, string $error)
    {
        preg_match('/(\d+)/', $error, $m);
        $wait = (int)($m[1] ?? 600);

        if ($row->type === 'infinity') {
            $newInterval = $this->smartInterval($wait);
            InfinityMessage::where('id', $row->id)->update([
                'interval' => $newInterval,
                'last_sent' => now(),
            ]);
        } else {
            TelegramMessage::where('id', $row->id)->update([
                'send_at' => now()->addSeconds($wait + 60),
                'attempts' => 0,
            ]);
        }
    }

    private function handleBan($row)
    {
        $model = $row->type === 'infinity' ? InfinityMessage::find($row->id) : TelegramMessage::find($row->id);
        if ($model) {
            $model->update(['status' => 'failed']);
            Log::warning("Doimiy ban: {$row->peer} | {$row->type}");
        }
    }

    private function smartInterval(int $wait): int
    {
        return match (true) {
            $wait < 300     => 300,
            $wait < 900     => 900,
            $wait < 3600    => 3600,
            $wait < 86400   => 86400,
            default         => 172800, // 48 soat
        };
    }

    private function failAll($messages): void
    {
        foreach ($messages as $row) {
            if ($row->type === 'infinity') {
                InfinityMessage::where('id', $row->id)->update(['status' => 'failed']);
            } else {
                TelegramMessage::where('id', $row->id)->update(['status' => 'failed']);
            }
        }
    }
}