<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAsUnread($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => null]);

        return back()->with('success', 'Notification marked as unread.');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $targetUserId = $request->user_id;

        if (!$targetUserId) {
            $users = User::all();
            foreach ($users as $user) {
                $this->broadcastNotification($user->id, $request->message);
            }
        } else {
            $this->broadcastNotification($targetUserId, $request->message);
        }

        return back()->with('success', 'Notification sent successfully!');
    }

    private function broadcastNotification($userId, $message)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => 'broadcast',
            'data' => [
                'message' => $message,
                'sender_id' => auth()->id(),
                'sender_name' => auth()->user()->name,
            ],
        ]);

        broadcast(new \App\Events\NotificationReceived($notification));
    }

    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function sendForm()
    {
        $users = User::all();
        return view('notifications.send', compact('users'));
    }
}
