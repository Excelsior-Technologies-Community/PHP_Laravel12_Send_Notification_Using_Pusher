<?php

namespace App\Http\Controllers;

use App\Events\NotificationReceived;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display all posts.
     */
    public function index()
    {
        $posts = Post::latest()->get();

        return view(
            'posts',
            compact('posts')
        );
    }

    /**
     * Create a new post and notify admins.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Post
        |--------------------------------------------------------------------------
        */

        $post = Post::create([
            'user_id' => auth()->id(),

            'title' => $request->title,

            'body' => $request->body,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Find Admin Users
        |--------------------------------------------------------------------------
        */

        $admins = User::where(
            'is_admin',
            1
        )->get();

        /*
        |--------------------------------------------------------------------------
        | Create Notification For Each Admin
        |--------------------------------------------------------------------------
        */

        foreach ($admins as $admin) {

            $notification = Notification::create([
                'user_id' => $admin->id,

                'type' => 'post',

                'data' => [
                    'message' =>
                        "New post created: {$post->title}",

                    'post_id' => $post->id,

                    'post_title' => $post->title,

                    'sender_id' => auth()->id(),

                    'sender_name' => auth()->user()->name,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Send Realtime Notification Through Pusher
            |--------------------------------------------------------------------------
            */

            broadcast(
                new NotificationReceived($notification)
            );
        }

        return back()->with(
            'success',
            'Post created successfully.'
        );
    }
}