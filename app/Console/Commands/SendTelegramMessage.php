<?php

namespace App\Console\Commands;

use App\Models\MessageGroup;
use App\Models\TelegramMessage;
use App\Models\UserPhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Application\Services\MadelineService;

class SendTelegramMessage extends Command
{
    protected $signature = 'telegram:send-messages {groupId}';
    protected $description = 'Send telegram messages for given message group';

    protected MadelineService $madelineService;

    // konfiguratsiya
    protected int $perMessageSpacingSeconds;
    protected int $maxAttempts;

    public function __construct(MadelineService $madelineService)
    {
        parent::__construct();
        $this->madelineService = $madelineService;
        $this->perMessageSpacingSeconds = 1;
        $this->maxAttempts = 3;
    }

    public function handle()
    {
        $groupId = (int) $this->argument('groupId');

        $group = MessageGroup::find($groupId);

        if (!$group) {
            $this->error("MessageGroup topilmadi: id={$groupId}");
            Log::warning("MessageGroup topilmadi: id={$groupId}");
            return self::FAILURE;
        }

        $userPhone = UserPhone::find($group->user_phone_id);

        if (!$userPhone) {
            $this->error("UserPhone topilmadi: id={$group->user_phone_id}");
            Log::warning("❌ UserPhone topilmadi: id={$group->user_phone_id}");
            $group->messages()->where('status', 'pending')->update(['status' => 'failed']);
            return self::FAILURE;
        }

        if (!$this->madelineService->validateAndStart($userPhone)) {
            $this->error("Session ishlamayapti: user_phone_id={$userPhone->id}");
            $group->messages()->where('status', 'pending')->update(['status' => 'failed']);
            return self::FAILURE;
        }

        $Madeline = $this->madelineService->getApi();

        // pending xabarlarni send_at bo'yicha oling
        $messages = $group->messages()
            ->where('status', 'pending')
            ->orderBy('send_at')
            ->get();

        foreach ($messages as $msgRow) {
            // yangi holatni DB-dan qayta oling (boshqalar o'zgargan bo'lishi mumkin)
            $message = TelegramMessage::find($msgRow->id);
            if (!$message || $message->status !== 'pending') {
                continue;
            }

            try {
                // Agar send_at kelajakda bo'lsa — kutamiz (komanda uzoq ishlaydi)
                if ($message->send_at && $message->send_at->isFuture()) {
                    $wait = $message->send_at->diffInSeconds(now());
                    if ($wait > 0) {
                        Log::info("Waiting {$wait}s for message id={$message->id}, peer={$message->peer}");
                        sleep($wait);
                    }
                }

                // peerlar orasidagi minimal spacing
                sleep($this->perMessageSpacingSeconds);

                // schedule_date uchun minimal talab (kamida hozir +60s)
                $minScheduleTs = now()->addSeconds(60)->timestamp;
                $schedule_date = $message->send_at && $message->send_at->timestamp > $minScheduleTs
                    ? $message->send_at->timestamp
                    : $minScheduleTs;

                $payload = [
                    'peer' => $message->peer,
                    'message' => $message->message_text,
                    'parse_mode' => 'HTML',
                    'schedule_date' => $schedule_date,
                ];

                $response = $Madeline->messages->sendMessage($payload);

                $telegramMessageId = null;
                $status = 'sent';

                if (($response['_'] ?? null) === 'updateShortSentMessage') {
                    $telegramMessageId = $response['id'] ?? null;
                    $status = 'sent';
                } elseif (($response['_'] ?? null) === 'updates') {
                    foreach ($response['updates'] as $update) {
                        if (($update['_'] ?? null) === 'updateNewScheduledMessage') {
                            $status = 'scheduled';
                            $telegramMessageId = $update['message']['id'] ?? null;
                            break;
                        }
                        if (($update['_'] ?? null) === 'updateNewMessage') {
                            $status = 'sent';
                            $telegramMessageId = $update['message']['id'] ?? null;
                            break;
                        }
                    }
                }

                $message->update([
                    'status' => $status,
                    'sent_at' => now(),
                    'telegram_message_id' => $telegramMessageId,
                    'attempts' => 0,
                ]);

                $this->info("✅ Xabar yuborildi: {$message->peer} (id={$message->id}, status={$status})");

            } catch (\Throwable $e) {
                $err = $e->getMessage();
                Log::error("❌ Xabar yuborilmadi: peer={$message->peer}, id={$message->id}", ['error' => $err]);

                // Attempts oshirish
                $message->increment('attempts');

                // FLOOD_WAIT ni aniqlash
                $waitSeconds = null;
                if (preg_match('/FLOOD_WAIT_(\d+)/i', $err, $m) || preg_match('/flood wait.*?(\d+)/i', $err, $m)) {
                    $waitSeconds = (int) $m[1];
                }

                if ($waitSeconds) {
                    $buffer = 5;
                    $newSendAt = now()->addSeconds($waitSeconds + $buffer);
                    $message->update(['send_at' => $newSendAt]);
                    Log::warning("FLOOD_WAIT for peer={$message->peer}, delaying message id={$message->id} to {$newSendAt}");
                    // davom etamiz (message hali pending), keyingi iteratsiyada bu xabar yana olinadi
                    continue;
                }

                // boshqa xatolar: agar attempts < limit — kutib keyinroq urinib ko'rish
                if ($message->attempts < $this->maxAttempts) {
                    $retryDelay = 10;
                    Log::info("Retrying message id={$message->id} after {$retryDelay}s (attempt={$message->attempts})");
                    sleep($retryDelay);
                    // qatorda yana bu message qayta sinalishi uchun DB ni o'zgartirmaymiz; lekin shu process ichida biz uni qayta tekshirish uchun
                    // qayta loop qilish uchun message ni qayta olishni xohlaysizmi — hozir davom etamiz.
                    // Bu yerni `continue` bilan ham ishlatish mumkin, ammo biz boshqa pending xabarlarga o'tamiz.
                    continue;
                }

                // attempts haddan oshsa failed qilamiz
                if ($message->attempts >= $this->maxAttempts) {
                    $message->update(['status' => 'failed']);
                    Log::error("Message permanently failed id={$message->id}, peer={$message->peer}");
                }
            }
        } // foreach messages

        $group->update(['status' => 'completed']);
        $this->info("🎉 Group yakunlandi: id={$groupId}");

        return self::SUCCESS;
    }
}
