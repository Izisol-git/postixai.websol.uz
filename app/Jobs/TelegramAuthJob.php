<?php

namespace App\Jobs;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TelegramAuthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public int $userId;

    public ?string $status;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phone,
        int $userId,
        ?string $status = 'add_phone'
    ) {
        $this->phone = $phone;
        $this->userId = $userId;
        $this->status = $status;
    }


    public function handle(): void
    {
        

        $php = '/opt/php83/bin/php';
        $artisan = base_path('artisan');

        $status = $this->status ?? 'add_phone';

        $command = sprintf(
            'nohup %s %s telegram:auth %s %d --status=%s > /dev/null 2>&1 &',
            $php,
            $artisan,
            escapeshellarg($this->phone),
            $this->userId,
            escapeshellarg($status)
        );

        exec($command);
    }
}
