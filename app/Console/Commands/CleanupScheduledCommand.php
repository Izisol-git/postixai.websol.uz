<?php

namespace App\Console\Commands;

use App\Models\MessageGroup;
use danog\MadelineProto\API;
use App\Models\UserPhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CleanupScheduledCommand extends Command
{
    protected $signature = 'telegram:cleanup-group {groupId}';
    protected $description = 'Delete scheduled messages from Telegram and mark as canceled in DB (per-message, peer+id checks)';

    public function handle()
    {
        $groupId = (int) $this->argument('groupId');

        $errors = [];
        $stats = [
            'canceled' => 0,
            'skipped_no_id' => 0,
            'skipped_sent' => 0,
            'delete_failed' => 0,
            'get_failed' => 0,
        ];

        $group = MessageGroup::with('messages')->find($groupId);
        if (!$group) {
            Log::error("Cleanup failed: group not found", ['group_id' => $groupId]);
            return Command::FAILURE;
        }

        $userPhone = UserPhone::find($group->user_phone_id);
        if (!$userPhone || !$userPhone->session_path) {
            Log::error("Cleanup failed: session not found", ['user_phone_id' => $group->user_phone_id]);
            return Command::FAILURE;
        }

        try {
            $Madeline = new API($userPhone->session_path);
            $Madeline->start();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            $shouldReset =
                str_contains($msg, 'AUTH_KEY_UNREGISTERED') ||
                str_contains($msg, 'SESSION_REVOKED') ||
                str_contains($msg, 'AUTH_KEY_INVALID');

            if ($shouldReset) {
                $path = $userPhone->session_path;
                if (File::exists($path)) {
                    File::isDirectory($path) ? File::deleteDirectory($path) : File::delete($path);
                }

                $userPhone->update(['session_path' => null, 'is_active' => false]);
                $group->messages()->where('status', 'pending')->update(['status' => 'failed']);
            }

            Log::error("Madeline start failed", [
                'user_phone_id' => $userPhone->id,
                'error' => $msg,
                'session_reset' => $shouldReset,
            ]);

            return Command::FAILURE;
        }

        $messages = $group->messages()
            ->whereIn('status', ['scheduled', 'pending', 'sent'])
            ->get();

        foreach ($messages as $message) {
            if (empty($message->telegram_message_id)) {
                $stats['skipped_no_id']++;
                continue;
            }

            $peer = (string)$message->peer;
            $tgId = (int)$message->telegram_message_id;

            $scheduledResp = null;

            // 1) Try getScheduledMessages
            try {
                $scheduledResp = $Madeline->messages->getScheduledMessages([
                    'peer' => $peer,
                    'id' => [$tgId],
                ]);
                // Log::info('Scheduled check response', ['local_message_id' => $message->id, 'telegram_message_id' => $tgId, 'response' => $scheduledResp]);
            } catch (\Throwable $e) {
                $errors[] = [
                    'type' => 'get_scheduled_failed',
                    'local_message_id' => $message->id,
                    'telegram_message_id' => $tgId,
                    'peer' => $peer,
                    'error' => $e->getMessage(),
                ];
                $stats['get_failed']++;
                $scheduledResp = null;
            }

            // STRICT RULE: if scheduledResp explicitly contains messageEmpty -> SKIP to next message
            if (is_array($scheduledResp)
                && isset($scheduledResp['messages'][0])
                && is_array($scheduledResp['messages'][0])
                && isset($scheduledResp['messages'][0]['_'])
                && $scheduledResp['messages'][0]['_'] === 'messageEmpty'
            ) {
                // Log::info('Ignoring messageEmpty from scheduled check', [
                //     'local_message_id' => $message->id,
                //     'telegram_message_id' => $tgId,
                //     'peer' => $peer,
                // ]);
                // do not change DB; move to next message
                continue;
            }

            // If not messageEmpty -> proceed with "old logic": attempt to delete scheduled and mark canceled.
            // (This matches your instruction: aempty bo'lmasa eski logika cancel schedule and change status to cancel)
            try {
                $Madeline->messages->deleteScheduledMessages([
                    'peer' => $peer,
                    'id' => [$tgId],
                ]);

                // If deleteScheduledMessages didn't throw - mark canceled
                try {
                    $message->update(['status' => 'canceled']);
                    $stats['canceled']++;
                } catch (\Throwable $eUpd) {
                    $errors[] = [
                        'type' => 'local_mark_cancel_failed',
                        'local_message_id' => $message->id,
                        'telegram_message_id' => $tgId,
                        'peer' => $peer,
                        'error' => $eUpd->getMessage(),
                    ];
                }
            } catch (\Throwable $eDel) {
                // deleteScheduledMessages threw an error
                $errors[] = [
                    'type' => 'delete_scheduled_failed',
                    'local_message_id' => $message->id,
                    'telegram_message_id' => $tgId,
                    'peer' => $peer,
                    'error' => $eDel->getMessage(),
                ];
                $stats['delete_failed']++;

                // still attempt to mark canceled locally as per "old logic"
                try {
                    $message->update(['status' => 'canceled']);
                    $stats['canceled']++;
                } catch (\Throwable $eUpd2) {
                    $errors[] = [
                        'type' => 'local_mark_cancel_failed_after_delete_error',
                        'local_message_id' => $message->id,
                        'error' => $eUpd2->getMessage(),
                    ];
                }
            }

            // next message in loop
        }

        Log::info("Cleanup finished", [
            'group_id' => $groupId,
            'stats' => $stats,
            'errors_count' => count($errors),
            'errors' => $errors ?: null,
        ]);

        return Command::SUCCESS;
    }
}
