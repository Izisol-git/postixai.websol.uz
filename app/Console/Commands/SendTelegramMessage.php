<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageGroup;
use App\Models\UserPhone;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings\Logger as LoggerSettings;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage extends Command
{
    protected $signature = 'telegram:send {group_id}';
    protected $description = 'Send pending Telegram messages for a given MessageGroup';

    public function handle()
    {
        $groupId = $this->argument('group_id');
        $group = MessageGroup::find($groupId);

        if (!$group) {
            $this->error("MessageGroup with id {$groupId} not found!");
            return 1;
        }

        $messages = $group->messages()->where('status', 'pending')->get();
        $userPhone = UserPhone::find($group->user_phone_id);

        if (!$userPhone || !file_exists($userPhone->session_path)) {
            $this->warn("❌ Session topilmadi: user_phone_id={$group->user_phone_id}");
            return 1;
        }

        $settings = new Settings;
        $settings->getAppInfo()
            ->setApiId(env('TELEGRAM_API_ID'))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $loggerSettings = (new LoggerSettings)->setType(Logger::FILE_LOGGER);
        $settings->setLogger($loggerSettings);
        
        $Madeline = new API($userPhone->session_path, $settings);
        $Madeline->start();

        foreach ($messages as $msg) {
            try {
                $Madeline->messages->sendMessage([
                    'peer'    => $msg->peer,
                    'message' => $msg->message_text,
                    'parse_mode' => 'HTML'
                ]);

                $msg->update([
                    'status'   => 'sent',
                    'sent_at'  => now(),
                    'attempts' => 0,
                ]);

                $this->info("✅ Message sent to {$msg->peer}");
            } catch (\Throwable $e) {
                Log::error("Telegram send failed for peer {$msg->peer}", [
                    'error' => $e->getMessage(),
                ]);
                $msg->update(['status' => 'failed']);
                $this->error("❌ Failed for {$msg->peer}");
                continue;
            }
        }

        $this->info("All messages processed for group {$groupId}");
        return 0;
    }
}
