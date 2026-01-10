<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyPhoneWithUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public string $code;
    public ?string $password;
    public ?int $departmentId;

    public function __construct(string $phone,  string $code, ?string $password = null, ?int $departmentId = null)
    {
        $this->phone = $phone;
        $this->code = $code;
        $this->password = $password;
        $this->departmentId = $departmentId;
    }

    public function handle(): void
    {
        $php = env('PHP_BIN', PHP_BINARY); // yoki '/opt/php83/bin/php' kabi kerakli yo‘lni envga qo‘y
        $artisan = base_path('artisan');

        // Escaping arguments
        $phoneArg = escapeshellarg($this->phone);
        $codeArg = escapeshellarg($this->code);
        $cmdParts = [
            escapeshellarg($php),
            escapeshellarg($artisan),
            'telegram:userVithPhone',
            $phoneArg,
            $codeArg
        ];

        if ($this->password) {
            $cmdParts[] = '--password=' . escapeshellarg($this->password);
        }

        if ($this->departmentId) {
            $cmdParts[] = '--department=' . escapeshellarg((string)$this->departmentId);
        }

        $command = implode(' ', $cmdParts);

        $background = $command . ' > /dev/null 2>&1 & echo $!';

        Log::info("TelegramVerifyJob: starting cli command: {$command}");

        exec($background, $output, $returnVar);

        $pid = isset($output[0]) ? (int) $output[0] : null;

        Log::info("TelegramVerifyJob: started command PID: " . ($pid ?? 'unknown') . " returnVar: {$returnVar}");
    }
}
