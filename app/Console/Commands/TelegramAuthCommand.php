<?php

namespace App\Console\Commands;

use App\Models\UserPhone;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use Illuminate\Console\Command;
use danog\MadelineProto\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use danog\MadelineProto\Settings\Logger as LoggerSettings;

class TelegramAuthCommand extends Command
{
    // --status option qo'shildi, default add_phone
    protected $signature = 'telegram:auth {phone} {userId} {--status=add_phone}';
    protected $description = 'Send Telegram auth code to a phone number directly, without queue';

    public function handle()
    {
        $phone = $this->argument('phone');
        $userId = $this->argument('userId');
        $status = $this->option('status') ?? 'add_phone';

        // allowed statuslarni tekshirish (xato qiymat bo'lsa chiqamiz)
        $allowed = ['add_phone', 'add_user'];
        if (!in_array($status, $allowed, true)) {
            $this->error("Invalid status: {$status}. Allowed: " . implode(', ', $allowed));
            Log::warning("telegram:auth called with invalid status", compact('phone', 'userId', 'status'));
            return 1;
        }

        Log::info("TelegramAuthCommand started", ['phone' => $phone, 'userId' => $userId, 'status' => $status]);
        $this->info("Starting Telegram auth for {$phone} (status: {$status})");

        // agar add_user bo'lsa session fayl nomida _add_user_ qo'shamiz
        if ($status === 'add_user') {
            $sessionPath = storage_path("app/sessions/{$phone}_add_user_{$userId}.madeline");
        } else {
            $sessionPath = storage_path("app/sessions/{$phone}_user_{$userId}.madeline");
        }

        if (file_exists($sessionPath)) {
            if (is_dir($sessionPath)) {
                \Illuminate\Support\Facades\File::deleteDirectory($sessionPath);
            } else {
                @unlink($sessionPath);
            }
            Log::info("Deleted existing session at {$sessionPath}");
            sleep(3);
        }

        if (!is_dir(dirname($sessionPath))) {
            mkdir(dirname($sessionPath), 0777, true);
            Log::info("Session directory created at " . dirname($sessionPath));
        }

        $settings = new Settings;
        $loggerSettings = (new LoggerSettings)->setType(Logger::FILE_LOGGER);
        $settings->setLogger($loggerSettings);
        $settings->setAppInfo(
            (new \danog\MadelineProto\Settings\AppInfo)
                ->setApiId(env('TELEGRAM_API_ID'))
                ->setApiHash(env('TELEGRAM_API_HASH'))
        );
        Log::info("MadelineProto settings prepared with API ID: " . env('TELEGRAM_API_ID'));

        $Madeline = new API($sessionPath, $settings);
        Log::info("MadelineProto API instance created for session {$sessionPath}");

        try {
            Log::info("Attempting phone login", ['phone' => $phone, 'status' => $status]);
            $Madeline->phoneLogin($phone);
            $this->info("SMS code sent successfully to {$phone}");
            Log::info("SMS code sent successfully to {$phone}");
        } catch (\Exception $e) {
            $this->error("Error sending code: " . $e->getMessage());
            Log::error("Error sending code", ['exception' => $e, 'phone' => $phone, 'status' => $status]);
        } finally {
            // lock keyga status qo'shildi
            $lockKey = "telegram_verify_lock_{$phone}_{$userId}_{$status}";
            Cache::forget($lockKey);
            Log::info("Lock cleared: {$lockKey}");
        }

        return 0;
    }
}
