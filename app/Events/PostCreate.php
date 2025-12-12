<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PostCreate Event
 *
 * This event is fired whenever a new post is created.
 * It implements ShouldBroadcastNow, which means:
 * - The event will broadcast IMMEDIATELY (no queue needed)
 * - Frontend listeners (Laravel Echo + Pusher) will receive it in real-time
 */
class PostCreate implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * The data that will be sent to the frontend.
     *
     * We store only the required fields from the Post model instead of passing
     * the entire Eloquent model to avoid unnecessary heavy serialization.
     */
    public $post;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Post  $post
     *
     * We convert the post into a lightweight array containing only the
     * necessary values (id, title, created_at).
     */
    public function __construct($post)
    {
        $this->post = [
            'id'         => $post->id,
            'title'      => $post->title,
            'created_at' => $post->created_at->toDateTimeString()
        ];
    }

    /**
     * The channel on which this event should broadcast.
     *
     * Here we use a PUBLIC CHANNEL named "posts".
     * All frontend listeners using window.Echo.channel('posts')
     * will receive this event.
     */
    public function broadcastOn()
    {
        return new Channel('posts');
    }

    /**
     * Custom event name to broadcast as.
     *
     * On the frontend, the listener will use:
     * window.Echo.channel('posts').listen('.create', ...)
     *
     * That ".create" comes from this method.
     */
    public function broadcastAs()
    {
        return 'create';
    }

    /**
     * Additional data to send with the event.
     *
     * This data becomes the payload received in JavaScript.
     * Example in frontend:
     * data.message → "New Post Received..."
     */
    public function broadcastWith()
    {
        return [
            'message' => "[{$this->post['created_at']}] New Post Received with title '{$this->post['title']}'."
        ];
    }
}
