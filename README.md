# PHP_Laravel12_Send_Notification_Using_Pusher


---

## Step 1: Install Laravel 12

```
composer create-project laravel/laravel example-app
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

```php
$posts = Post::latest()->get();
return view('posts', compact('posts'));
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

This file includes:

- Navbar  
- Vite assets (`app.js`, Echo)  
- Font Awesome  
- A section for custom scripts (`@yield('script')`)

**Explanation:**  
Echo listener will be injected dynamically inside child views.

---

### Create posts.blade.php

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
