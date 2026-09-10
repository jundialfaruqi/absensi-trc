<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonnelVectorUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $personnel_id;
    public ?int $opd_id;
    public string $status;

    /**
     * Create a new event instance.
     */
    public function __construct(int $personnel_id, ?int $opd_id, string $status = 'ready')
    {
        $this->personnel_id = $personnel_id;
        $this->opd_id = $opd_id;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('personnel-biometrics'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PersonnelVectorUpdated';
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
            'status' => $this->status,
            'timestamp' => now()->toISOString(),
        ];
    }
}
