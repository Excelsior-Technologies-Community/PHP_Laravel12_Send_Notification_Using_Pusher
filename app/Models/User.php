<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * The User model represents each registered user inside the application.
 *
 * It extends Laravel's Authenticatable class, which provides all
 * built-in authentication features like login, registration, and password hashing.
 *
 * A user can create posts, and admin users have extra permissions.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that can be mass-assigned.
     *
     * These fields are allowed to be filled through create() or update() methods.
     * We include 'is_admin' to allow specifying whether a user is normal or admin.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden when user data is converted to JSON.
     *
     * This ensures sensitive data like passwords and tokens never appear
     * in API responses or logs.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting allows Laravel to automatically convert values
     * into specific data types when retrieving from or saving to the database.
     *
     * - email_verified_at: automatically converted to DateTime object
     * - password: automatically hashed using Laravel's default hashing system
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',   // auto-hashes password when assigning
        ];
    }

    /**
     * Relationship: A user can have many posts.
     *
     * This defines:
     * User → hasMany(Post::class)
     *
     * Example usage:
     * $user->posts;  // returns all posts created by the user
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
