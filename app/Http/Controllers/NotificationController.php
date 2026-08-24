<?php

namespace App\Http\Controllers;

use App\Events\NotificationReceived;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display notification history.
     */
    public function index()
    {
        $notifications = Notification::where(
                'user_id',
                auth()->id()
            )
            ->latest()
            ->paginate(20);

        return view(
            'notifications.index',
            compact('notifications')
        );
    }

    /**
     * Return notifications for navbar dropdown.
     */
    public function dropdown()
    {
        $notifications = Notification::where(
                'user_id',
                auth()->id()
            )
            ->latest()
            ->take(10)
            ->get();

        return response()->json(
            $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,

                    'message' =>
                        $notification->data['message']
                        ?? 'Notification',

                    'type' =>
                        $notification->type,

                    'sender_name' =>
                        $notification->data['sender_name']
                        ?? 'System',

                    'post_id' =>
                        $notification->data['post_id']
                        ?? null,

                    'read' =>
                        !is_null($notification->read_at),

                    'created_at' =>
                        $notification->created_at
                            ->diffForHumans(),

                    'created_at_full' =>
                        $notification->created_at
                            ->format('d M Y H:i:s'),
                ];
            })
        );
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::where(
                'user_id',
                auth()->id()
            )
            ->where('id', $id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread($id)
    {
        $notification = Notification::where(
                'user_id',
                auth()->id()
            )
            ->where('id', $id)
            ->firstOrFail();

        $notification->update([
            'read_at' => null,
        ]);

        return back()->with(
            'success',
            'Notification marked as unread.'
        );
    }

    /**
     * Send notification.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => [
                'required',
                'string',
                'max:500',
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],
        ]);

        $targetUserId = $request->user_id;

        if (!$targetUserId) {
            $users = User::all();

            foreach ($users as $user) {
                $this->broadcastNotification(
                    $user->id,
                    $request->message
                );
            }
        } else {
            $this->broadcastNotification(
                $targetUserId,
                $request->message
            );
        }

        return back()->with(
            'success',
            'Notification sent successfully!'
        );
    }

    /**
     * Create notification and broadcast through Pusher.
     */
    private function broadcastNotification(
        int $userId,
        string $message
    ): void {
        $notification = Notification::create([
            'user_id' => $userId,

            'type' => 'broadcast',

            'data' => [
                'message' => $message,

                'sender_id' => auth()->id(),

                'sender_name' => auth()->user()->name,
            ],
        ]);

        broadcast(
            new NotificationReceived($notification)
        );
    }

    /**
     * Return current unread notification count.
     */
    public function unreadCount()
    {
        $count = Notification::where(
                'user_id',
                auth()->id()
            )
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Notification sending form.
     */
    public function sendForm()
    {
        $users = User::orderBy('name')->get();

        return view(
            'notifications.send',
            compact('users')
        );
    }
}