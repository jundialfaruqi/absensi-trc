<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $token,
        public string $title,
        public string $body,
        public array $data = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcmService): void
    {
        $sent = $fcmService->sendNotification($this->token, $this->title, $this->body, $this->data);

        if (! $sent) {
            Log::error('SendFcmNotificationJob: Gagal mengirim FCM ke token '.substr($this->token, 0, 10).'...');
        }
    }
}
