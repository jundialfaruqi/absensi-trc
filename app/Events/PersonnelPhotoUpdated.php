<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonnelPhotoUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $personnel_id;
    public ?int $opd_id;
    public string $name;
    public string $foto_url;

    /**
     * Create a new event instance.
     */
    public function __construct(int $personnel_id, ?int $opd_id, string $name, string $foto_url)
    {
        $this->personnel_id = $personnel_id;
        $this->opd_id = $opd_id;
        $this->name = $name;
        $this->foto_url = $foto_url;
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
        return 'PersonnelPhotoUpdated';
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
            'name' => $this->name,
            'foto_url' => $this->foto_url,
            'timestamp' => now()->toISOString(),
        ];
    }
}
