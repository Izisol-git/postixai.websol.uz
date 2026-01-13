<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MarkPastScheduledAsSentCommand extends Command
{
    protected $signature = 'telegram:mark-past-scheduled {--daemon : Run as daemon (infinite loop)} {--sleep=60 : Sleep seconds between loops in daemon mode}';
    protected $description = 'Mark scheduled messages as sent if their send_at time has already passed';

    // table name — o'zgartirish kerak bo'lsa config orqali
    protected $tableKey = 'telegram_messages';

    public function handle()
    {
        $isDaemon = $this->option('daemon');
        $sleep = (int) $this->option('sleep');

        if ($isDaemon) {
            $this->info("Daemon mode started (sleep={$sleep}s). Use Ctrl+C to stop.");
            // optional: try graceful signals
            if (function_exists('pcntl_signal')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGTERM, function () {
                    $this->info("Daemon stopping (SIGTERM).");
                    exit;
                });
                pcntl_signal(SIGINT, function () {
                    $this->info("Daemon stopping (SIGINT).");
                    exit;
                });
            }

            while (true) {
                $this->processOnce();
                sleep($sleep);
            }
        }

        // one-off run
        $this->processOnce();

        return Command::SUCCESS;
    }

    protected function processOnce(): void
    {
        $table = config($this->tableKey, 'telegram_messages'); // default 'messages', change in config if needed
        $nowUtc = Carbon::now()->utc()->toDateTimeString();

        try {
            // 1) first, collect ids to update (avoid race conditions, use transaction)
            $rows = DB::table($table)
                ->where('status', 'scheduled')
                ->where('send_at', '<=', $nowUtc)
                ->select('id')
                ->get();

            if ($rows->isEmpty()) {
                Log::info("telegram:mark-past-scheduled — nothing to update at {$nowUtc}");
                return;
            }

            $ids = $rows->pluck('id')->toArray();

            // 2) update in a transaction
            DB::transaction(function () use ($table, $ids, $nowUtc) {
                $updated = DB::table($table)
                    ->whereIn('id', $ids)
                    ->where('status', 'scheduled') // extra guard
                    ->update([
                        'status' => 'sent',
                        'sent_at' => $nowUtc,
                        'updated_at' => $nowUtc,
                    ]);

                Log::info("telegram:mark-past-scheduled — updated {$updated} rows (ids: " . implode(',', array_slice($ids, 0, 10)) . (count($ids) > 10 ? ',...' : '') . ")");
            });
        } catch (\Throwable $e) {
            Log::error("telegram:mark-past-scheduled — error: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
