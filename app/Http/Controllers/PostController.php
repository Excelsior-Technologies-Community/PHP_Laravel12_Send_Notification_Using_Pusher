<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Events\NotificationReceived;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();
        return view('posts', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body'  => 'required'
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title'   => $request->title,
            'body'    => $request->body,
        ]);

        foreach (User::all() as $user) {
            $notification = \App\Models\Notification::create([
                'user_id' => $user->id,
                'type'    => 'post',
                'data'    => [
                    'message' => "New post created: {$post->title}",
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                ],
            ]);

            broadcast(new NotificationReceived($notification));
        }

        return back()->with('success', 'Post created successfully.');
    }
}
