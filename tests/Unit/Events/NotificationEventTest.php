<?php

namespace Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use App\Events\NotificationEvent;
use Illuminate\Broadcasting\PrivateChannel;

class NotificationEventTest extends TestCase
{
    public function test_broadcast_channel()
    {
        $data = [
            'noti' => []
        ];

        $event = new NotificationEvent($data);
        $channel = $event->broadcastOn();
        $this->assertEquals('channel-notification', $channel->name);
    }
}
