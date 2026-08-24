<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationReceived implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = [
            'id'         => $notification->id,
            'user_id'    => $notification->user_id,
            'type'       => $notification->type,
            'data'       => $notification->data,
            'read_at'    => $notification->read_at,
            'created_at' => $notification->created_at->toDateTimeString(),
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('notifications');
    }

    public function broadcastAs(): string
    {
        return 'received';
    }
}