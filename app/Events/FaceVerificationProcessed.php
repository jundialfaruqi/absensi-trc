<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FaceVerificationProcessed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $personnel_id;
    public string $status; // 'APPROVED' | 'REJECTED'
    public ?string $notes;
    public ?string $verified_at;

    /**
     * Create a new event instance.
     */
    public function __construct(int $personnel_id, string $status, ?string $notes = null, ?string $verified_at = null)
    {
        $this->personnel_id = $personnel_id;
        $this->status = $status;
        $this->notes = $notes;
        $this->verified_at = $verified_at ?: now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('personnel-' . $this->personnel_id),
            new Channel('admin-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'FaceVerificationProcessed';
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
            'status' => $this->status,
            'notes' => $this->notes,
            'verified_at' => $this->verified_at,
            'type' => 'FACE_VERIFICATION_PROCESSED',
            'timestamp' => now()->toISOString(),
        ];
    }
}
