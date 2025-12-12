<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Events\PostCreate;

/**
 * PostController
 *
 * This controller handles all operations related to posts.
 * - Displaying list of posts
 * - Creating new posts
 * - Broadcasting realtime notifications using Pusher
 */
class PostController extends Controller
{
    /**
     * Display a listing of posts.
     *
     * This method fetches all posts from the database ordered by
     * latest first and returns them to the "posts" Blade view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all posts, newest first
        $posts = Post::latest()->get();

        // Send the posts collection to the Blade view
        return view('posts', compact('posts'));
    }

    /**
     * Store a newly created post in the database.
     *
         * This method:
     * 1. Validates the incoming request data
     * 2. Creates a new post in the database
     * 3. Fires a broadcasting event (PostCreate) to notify admin users
     * 4. Redirects back with a success message
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate required form inputs
        $request->validate([
            'title' => 'required',
            'body'  => 'required'
        ]);

        /**
         * Create a new post record in the database.
         * 
         * - auth()->id() returns the logged-in user's ID.
         * - fillable fields must be defined in Post model.
         */
        $post = Post::create([
            'user_id' => auth()->id(),   // Associate post with logged-in user
            'title'   => $request->title,
            'body'    => $request->body,
        ]);

        /**
         * Broadcast the newly created post to the Pusher channel.
         * Admin users listening via Laravel Echo will receive
         * a realtime notification instantly.
         */
        event(new PostCreate($post));

        // Redirect back to the posts page with success message
        return back()->with('success', 'Post created successfully.');
    }
}
