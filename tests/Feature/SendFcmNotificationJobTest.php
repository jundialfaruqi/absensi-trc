<?php

use App\Jobs\SendFcmNotificationJob;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

test('job calls FcmService to send notification', function () {
    $token = 'test-token-123';
    $title = 'Test Title';
    $body = 'Test Body';
    $data = ['key' => 'value'];

    $mockFcm = $this->mock(FcmService::class);
    $mockFcm->shouldReceive('sendNotification')
        ->once()
        ->with($token, $title, $body, $data)
        ->andReturn(true);

    $job = new SendFcmNotificationJob($token, $title, $body, $data);
    $job->handle($mockFcm);
});

test('job logs error when FcmService fails to send notification', function () {
    $token = 'test-token-123';
    $title = 'Test Title';
    $body = 'Test Body';
    $data = ['key' => 'value'];

    $mockFcm = $this->mock(FcmService::class);
    $mockFcm->shouldReceive('sendNotification')
        ->once()
        ->with($token, $title, $body, $data)
        ->andReturn(false);

    Log::shouldReceive('error')
        ->once()
        ->with('SendFcmNotificationJob: Gagal mengirim FCM ke token test-token...');

    $job = new SendFcmNotificationJob($token, $title, $body, $data);
    $job->handle($mockFcm);
});
