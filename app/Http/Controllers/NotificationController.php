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
     *
     * New functionality:
     * - Search notifications
     * - Filter by type
     * - Filter by read/unread status
     * - Pagination
     */
    public function index(Request $request)
    {
        $query = Notification::where(
            'user_id',
            auth()->id()
        );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('type', 'like', "%{$search}%")
                    ->orWhereJsonContains(
                        'data->message',
                        $search
                    )
                    ->orWhereJsonContains(
                        'data->sender_name',
                        $search
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter By Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {

            $query->where(
                'type',
                $request->type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter By Read / Unread
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'read') {

            $query->whereNotNull('read_at');
        } elseif ($request->status === 'unread') {

            $query->whereNull('read_at');
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $notifications = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Notification Statistics
        |--------------------------------------------------------------------------
        */

        $totalNotifications = Notification::where(
            'user_id',
            auth()->id()
        )->count();

        $unreadNotifications = Notification::where(
            'user_id',
            auth()->id()
        )
            ->whereNull('read_at')
            ->count();

        $readNotifications = Notification::where(
            'user_id',
            auth()->id()
        )
            ->whereNotNull('read_at')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Available Notification Types
        |--------------------------------------------------------------------------
        */

        $notificationTypes = Notification::where(
            'user_id',
            auth()->id()
        )
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'notifications.index',
            compact(
                'notifications',
                'totalNotifications',
                'unreadNotifications',
                'readNotifications',
                'notificationTypes'
            )
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
            ->oldest()
            ->take(5)
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
     * NEW FUNCTIONALITY 1
     *
     * Delete one notification.
     */
    public function destroy($id)
    {
        $notification = Notification::where(
            'user_id',
            auth()->id()
        )
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return back()->with(
            'success',
            'Notification deleted successfully.'
        );
    }

    /**
     * NEW FUNCTIONALITY 1
     *
     * Delete all notifications of current user.
     */
    public function destroyAll()
    {
        Notification::where(
            'user_id',
            auth()->id()
        )->delete();

        return back()->with(
            'success',
            'All notifications deleted successfully.'
        );
    }

    /**
     * NEW FUNCTIONALITY 2
     *
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where(
            'user_id',
            auth()->id()
        )
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
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

        /*
        |--------------------------------------------------------------------------
        | Send To All Users
        |--------------------------------------------------------------------------
        */

        if (!$targetUserId) {

            $users = User::all();

            foreach ($users as $user) {

                $this->broadcastNotification(
                    $user->id,
                    $request->message
                );
            }
        } else {

            /*
            |--------------------------------------------------------------------------
            | Send To One User
            |--------------------------------------------------------------------------
            */

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
