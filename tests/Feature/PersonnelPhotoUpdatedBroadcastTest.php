<?php

namespace Tests\Feature;

use App\Events\PersonnelPhotoUpdated;
use App\Events\PersonnelVectorUpdated;
use Tests\TestCase;

class PersonnelPhotoUpdatedBroadcastTest extends TestCase
{
    public function test_personnel_photo_updated_broadcast_payload(): void
    {
        $event = new PersonnelPhotoUpdated(42, 5, 'Rahmat Hidayat', 'https://example.com/foto.jpg');

        $this->assertEquals(['personnel-biometrics'], array_map(fn($c) => $c->name, $event->broadcastOn()));
        $payload = $event->broadcastWith();

        $this->assertEquals(42, $payload['personnel_id']);
        $this->assertEquals(5, $payload['opd_id']);
        $this->assertEquals('Rahmat Hidayat', $payload['name']);
        $this->assertEquals('https://example.com/foto.jpg', $payload['foto_url']);
        $this->assertArrayHasKey('timestamp', $payload);
    }

    public function test_personnel_vector_updated_broadcast_payload(): void
    {
        $event = new PersonnelVectorUpdated(42, 5, 'ready');

        $this->assertEquals(['personnel-biometrics'], array_map(fn($c) => $c->name, $event->broadcastOn()));
        $payload = $event->broadcastWith();

        $this->assertEquals(42, $payload['personnel_id']);
        $this->assertEquals(5, $payload['opd_id']);
        $this->assertEquals('ready', $payload['status']);
        $this->assertArrayHasKey('timestamp', $payload);
    }
}
