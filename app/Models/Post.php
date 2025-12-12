<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The Post model represents a single blog post entry in the database.
 * Each post belongs to a user, and contains a title and body text.
 *
 * This model uses Laravel Eloquent ORM to interact with the "posts" table.
 */
class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * These fields can be filled automatically using methods like:
     * Post::create([...]) or $post->update([...])
     *
     * We add 'title', 'body', and 'user_id' because these values will be
     * coming from user input or controller.
     */
    protected $fillable = ['title', 'body', 'user_id'];

    /**
     * Relationship: A post belongs to one user.
     *
     * This function defines the inverse relationship of:
     * User hasMany(Post::class)
     *
     * It allows us to access the user who created the post:
     * $post->user->name
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
