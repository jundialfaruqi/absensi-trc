<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonnelLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $personnel_id;
    public $latitude;
    public $longitude;
    public $last_seen;

    /**
     * Create a new event instance.
     */
    public function __construct($personnel_id, $latitude, $longitude, $last_seen)
    {
        $this->personnel_id = $personnel_id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->last_seen = $last_seen;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('personnel-locations'),
        ];
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'last_seen' => $this->last_seen,
        ];
    }
}
