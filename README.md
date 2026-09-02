# PHP_Laravel12_Send_Notification_Using_Pusher


---

## Step 1: Install Laravel 12

```
composer create-project laravel/laravel example-app
```
# Now Setup .env file for database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=root
DB_PASSWORD=
```
**Explanation:**  
Only needed if you haven't created your Laravel project yet.

---

## Step 2: Create Authentication Scaffold (Login/Register)

Install Laravel UI:

```
composer require laravel/ui
```

Generate Bootstrap Auth UI:

```
php artisan ui bootstrap --auth
npm install
npm run build
```

**Explanation:**  
This creates Login, Register, Dashboard, and authentication routes that the project will use.

---

## Step 3: Create Migrations (Users + Posts)

### Add `is_admin` column to users table

```
php artisan make:migration add_is_admin_column_table
```

Migration:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->tinyInteger('is_admin')->default(0);
    });
}
```

**Explanation:**  
This field identifies admin users who will receive realtime notifications.

---

### Create posts table

```
php artisan make:migration create_posts_table
```

Migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});
```

**Explanation:**  
Each post belongs to a user, and when a post is created, a realtime notification will be triggered.

---

Run migrations:

```
php artisan migrate
```

---

## Step 4: Create and Update Models

### Post Model

```
php artisan make:model Post
```

`app/Models/Post.php`:

```php
class Post extends Model
{
    protected $fillable = ['title', 'body', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

**Explanation:**  
Defines mass-assignable fields and the relationship:  
➡️ *A post belongs to one user.*

---

### Update User Model

`app/Models/User.php`:

```php
protected $fillable = ['name','email','password','is_admin'];

public function posts()
{
    return $this->hasMany(Post::class);
}
```

**Explanation:**  
Adds admin flag and relationship:  
➡️ *A user can create many posts.*

---

## Step 5: Create Pusher App

Create an account on **pusher.com** and obtain:

- App ID  
- Key  
- Secret  
- Cluster  

These will be stored in `.env`.
<img width="1003" height="443" alt="image" src="https://github.com/user-attachments/assets/8f969c09-bd14-4a4f-b8a0-41c14152fad4" />
<img width="970" height="471" alt="image" src="https://github.com/user-attachments/assets/f2763b13-9c1d-4b0e-be6d-c690e69fcf16" />
<img width="975" height="466" alt="image" src="https://github.com/user-attachments/assets/4ad3d380-8ba4-42dc-963e-0db380451978" />


**Explanation:**  
Pusher handles the realtime WebSocket communication.

---

## Step 6: Setup Pusher & Laravel Echo

Enable broadcasting:

```
php artisan install:broadcasting
```

Install Pusher server:

```
composer require pusher/pusher-php-server
```

Install Echo & Pusher JS:

```
npm install --save-dev laravel-echo pusher-js
```

---

### Update `resources/js/echo.js`

(Your document includes full code; here is the purpose:)

**Explanation:**  
This file connects Laravel → Echo → Pusher using WebSockets.  
It enables the frontend to listen for broadcasted events in realtime.

---

### Add Pusher credentials to `.env`

```
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
PUSHER_PORT=443
```

**Explanation:**  
Laravel uses these settings to authenticate with Pusher.

---

Rebuild assets:

```
npm run build
```

---

## Step 7: Create PostCreate Event

```
php artisan make:event PostCreate
```

`app/Events/PostCreate.php`:

**Explanation of important parts:**

- Implements `ShouldBroadcastNow` → event broadcasts immediately  
- Uses channel `posts`  
- Sends notification data via `broadcastWith()`  
- Uses `broadcastAs()` → event name = **create**

Example broadcast name:

```
.posts → .create
```

---

## Step 8: Add Routes

`routes/web.php` includes:

- Authentication routes  
- Homepage route  
- Protected routes (`/posts`)  

Important:

```php
Route::middleware('auth')->group(function () {

    Route::get('/posts', [PostController::class, 'index'])
        ->name('posts.index');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

});
```

**Explanation:**  
Users must be logged in to create posts.  
Admin users will receive realtime notifications of new posts.

---

## Step 9: Create PostController

`app/Http/Controllers/PostController.php`:

### index()

```<?php

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

```

**Explanation:**  
Loads all posts and displays them in a table.

---

### store()

Validates input → Creates post → Broadcasts event:

```php
event(new PostCreate($post));
```

**Explanation:**  
This triggers a realtime Pusher notification for admin users.

---

## Step 10: Create and Update Blade Views

### Update layout `app.blade.php`
```<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- 
        CSRF Token 
        --------------------------------------------------------------
        This token protects your application from Cross-Site Request 
        Forgery attacks. It is automatically included in all AJAX
        requests and Laravel forms.
    -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- 
        Application Title
        --------------------------------------------------------------
        Uses value from config/app.php, fallback = "Laravel"
    -->
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts (Loaded from Bunny CDN for speed) -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- 
        Vite Asset Loading 
        --------------------------------------------------------------
        This loads both:
        - app.scss (Bootstrap + custom styles)
        - app.js   (Bootstrap JS + Echo + Pusher)
        
        Vite compiles, bundles & refreshes these assets.
    -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- 
        Extra Scripts Section 
        --------------------------------------------------------------
        This is used by child Blade views to inject custom JavaScript.
        Example: posts.blade.php injects real-time Echo listener script here.
    -->
    @yield('script')
</head>

<body>
    <div id="app">

        <!-- 
            Navbar Component 
            --------------------------------------------------------------
            This navigation bar is visible on every page. It displays:
            - Brand name
            - Login & Register buttons for guests
            - Posts link + Username + Logout button for authenticated users
        -->
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">

                <!-- App Brand / Home Link -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    Laravel Send Realtime Notification using Pusher
                </a>

                <!-- Responsive Toggle Button (Mobile View) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar Menu -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <!-- Left Side Empty (you can add menu items here later) -->
                    <ul class="navbar-nav me-auto"></ul>

                    <!-- Right Side Menu -->
                    <ul class="navbar-nav ms-auto">

                        <!-- If user is NOT logged in -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <!-- Show Posts link only when user is logged in -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('posts.index') }}">{{ __('Posts') }}</a>
                            </li>

                            <!-- User Dropdown Menu -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                                    <!-- Logout Button -->
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <!-- Hidden Logout Form -->
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>

                                </div>
                            </li>
                        @endguest

                    </ul>
                </div>
            </div>
        </nav>

        <!-- 
            Main Content Section 
            --------------------------------------------------------------
            All pages load their main content here through @yield('content').
        -->
        <main class="py-4">
            @yield('content')
        </main>

    </div>
</body>

</html>
```
This file includes:

- Navbar  
- Vite assets (`app.js`, Echo)  
- Font Awesome  
- A section for custom scripts (`@yield('script')`)

**Explanation:**  
Echo listener will be injected dynamically inside child views.

---

### Create posts.blade.php
```@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><i class="fa fa-list"></i> {{ __('Posts List') }}</div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert"> 
                            {{ session('success') }}
                        </div>
                    @endif

                    <div id="notification"></div>

                    {{-- Show form ONLY if user is logged in AND NOT admin --}}
                    @if(auth()->check() && !auth()->user()->is_admin)
                        <p><strong>Create New Post</strong></p>
                        <form method="post" action="{{ route('posts.store') }}">
                            @csrf
                            <div class="form-group">
                                <label>Title:</label>
                                <input type="text" name="title" class="form-control" />
                                @error('title')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Body:</label>
                                <textarea class="form-control" name="body"></textarea>
                                @error('body')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mt-2">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fa fa-save"></i> Submit
                                </button>
                            </div>
                        </form>
                    @endif

                    <p class="mt-4"><strong>Post List:</strong></p>
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th width="70px">ID</th>
                                <th>Title</th>
                                <th>Body</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->body }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">There are no posts.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
{{-- Only ADMIN should receive realtime notifications --}}
@if(auth()->check() && auth()->user()->is_admin)
<script type="module">
    window.Echo.channel('posts')
        .listen('.create', (data) => {
            console.log('New Post Event Received:', data);
            document.getElementById('notification')
                .insertAdjacentHTML(
                    'beforeend', 
                    `<div class="alert alert-success alert-dismissible fade show">
                        <span><i class="fa fa-circle-check"></i> ${data.message}</span>
                    </div>`
                );
        });
</script>
@endif
@endsection
```
Features:

- Form for creating new posts (only normal users)  
- Table showing posts  
- Admin-only realtime notification listener  

### Important:

```javascript
window.Echo.channel('posts')
    .listen('.create', (data) => {
        document.getElementById('notification')
            .insertAdjacentHTML('beforeend',
                `<div class="alert alert-success">${data.message}</div>`
            );
    });
```

**Explanation:**  
Admin sees instant notifications when any user creates a post.

---

## Step 11: Create Admin User

```
php artisan make:seeder CreateAdminUser
```

Seeder:

```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@gmail.com',
    'password' => bcrypt('123456'),
    'is_admin' => 1
]);
```

Run seeder:

```
php artisan db:seed --class=CreateAdminUser
```

**Explanation:**  
Admin user receives realtime Pusher notifications.

---

## Run Laravel App

Build & Run:

```
npm run dev
npm run build
php artisan serve
```

Open:

```
http://localhost:8000/posts
```
<img width="1646" height="502" alt="image" src="https://github.com/user-attachments/assets/8d70abfb-c537-437c-b12a-d841460ae178" />
<img width="1657" height="735" alt="image" src="https://github.com/user-attachments/assets/74d06f40-3863-4e93-a94f-9368474b80d3" />

# Now Open  Ctrl+Shift+N new private window and run this url and login to normal user and create any posts and  show below image to successful send notification to admin side to create  post 
```

http://localhost:8000/posts
```

<img width="1646" height="502" alt="image" src="https://github.com/user-attachments/assets/40c871f1-ff79-4a75-9f19-a5d681326b68" />
<img width="1599" height="657" alt="image" src="https://github.com/user-attachments/assets/21f0820b-9fdd-4498-be70-397444babdcb" />
