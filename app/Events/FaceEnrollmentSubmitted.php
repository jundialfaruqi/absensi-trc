<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FaceEnrollmentSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $personnel_id;
    public ?int $opd_id;
    public string $opd_name;
    public string $name;
    public string $nik;
    public ?string $foto_url;

    /**
     * Create a new event instance.
     */
    public function __construct(int $personnel_id, ?int $opd_id, string $opd_name, string $name, string $nik, ?string $foto_url = null)
    {
        $this->personnel_id = $personnel_id;
        $this->opd_id = $opd_id;
        $this->opd_name = $opd_name;
        $this->name = $name;
        $this->nik = $nik;
        $this->foto_url = $foto_url;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('admin-notifications'),
            new Channel('personnel-biometrics'),
        ];

        if ($this->opd_id) {
            $channels[] = new Channel('admin-opd-' . $this->opd_id);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'FaceEnrollmentSubmitted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'personnel_id' => $this->personnel_id,
            'opd_id' => $this->opd_id,
            'opd_name' => $this->opd_name,
            'name' => $this->name,
            'nik' => $this->nik,
            'foto_url' => $this->foto_url,
            'type' => 'FACE_ENROLLMENT_SUBMITTED',
            'timestamp' => now()->toISOString(),
        ];
    }
}
