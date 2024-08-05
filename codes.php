<?php

// Laravel Project Initialization and Setup
/*
 * Step 1: Install Laravel
 * composer create-project --prefer-dist laravel/laravel myapp "10.*"
 *
 * Step 2: Set Up Authentication
 * composer require laravel/breeze --dev
 * php artisan breeze:install
 * php artisan migrate
 * npm install && npm run dev
 */

// Post Model, Migration, and Controller Setup
/*
 * php artisan make:model Post -mcr
 */

// Migration File: database/migrations/yyyy_mm_dd_create_posts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable(); // Adding image column
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}

// Route Definition: routes/web.php
use App\Http\Controllers\PostController;

Route::resource('posts', PostController::class);
Route::get('/profile', [UserController::class, 'profile'])->name('profile');

// Post Controller: app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validating image
        ]);

        $post = new Post;
        $post->title = $request->title;
        $post->body = $request->body;

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path('images'), $imageName);
            $post->image = $imageName;
        }

        $post->save();

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validating image
        ]);

        $post->title = $request->title;
        $post->body = $request->body;

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path('images'), $imageName);
            $post->image = $imageName;
        }

        $post->save();

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}

// User Controller: app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile()
    {
        return view('profile');
    }
}

// Blade Views for Post CRUD Operations: resources/views/posts/

// index.blade.php
@extends('layout')

@section('content')
    <h1>Posts</h1>
    <a href="{{ route('posts.create') }}">Create Post</a>
    @foreach ($posts as $post)
        <div>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->body }}</p>
            @if ($post->image)
                <img src="{{ asset('images/' . $post->image) }}" width="100">
            @endif
            <a href="{{ route('posts.show', $post->id) }}">View</a>
            <a href="{{ route('posts.edit', $post->id) }}">Edit</a>
            <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
    @endforeach
@endsection

// create.blade.php
@extends('layout')

@section('content')
    <h1>Create Post</h1>
    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Title:</label>
        <input type="text" name="title" required>
        <label>Body:</label>
        <textarea name="body" required></textarea>
        <label>Image:</label>
        <input type="file" name="image">
        <button type="submit">Create</button>
    </form>
@endsection

// edit.blade.php
@extends('layout')

@section('content')
    <h1>Edit Post</h1>
    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label>Title:</label>
        <input type="text" name="title" value="{{ $post->title }}" required>
        <label>Body:</label>
        <textarea name="body" required>{{ $post->body }}</textarea>
        <label>Image:</label>
        <input type="file" name="image">
        @if ($post->image)
            <img src="{{ asset('images/' . $post->image) }}" width="100">
        @endif
        <button type="submit">Update</button>
    </form>
@endsection

// show.blade.php
@extends('layout')

@section('content')
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->body }}</p>
    @if ($post->image)
        <img src="{{ asset('images/' . $post->image) }}" width="300">
    @endif
    <a href="{{ route('posts.index') }}">Back to Posts</a>
@endsection

// Profile Blade View: resources/views/profile.blade.php
@extends('layout')

@section('content')
    <h1>Profile Page</h1>
    <p>Welcome, {{ Auth::user()->name }}!</p>
    <p>Email: {{ Auth::user()->email }}</p>
@endsection

// Layout Blade View: resources/views/layout.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Laravel CRUD</title>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>

// Commonly Used Queries in Laravel
/*
 * 1. Retrieving All Records:
 * $users = User::all();
 *
 * 2. Retrieving a Single Record by Primary Key:
 * $user = User::find(1);
 *
 * 3. Retrieving a Single Record by a Column Value:
 * $user = User::where('email', 'example@example.com')->first();
 *
 * 4. Inserting a New Record:
 * $user = new User;
 * $user->name = 'John Doe';
 * $user->email = 'john@example.com';
 * $user->password = bcrypt('password');
 * $user->save();
 *
 * 5. Updating an Existing Record:
 * $user = User::find(1);
 * $user->name = 'Jane Doe';
 * $user->save();
 *
 * 6. Deleting a Record:
 * $user = User::find(1);
 * $user->delete();
 *
 * 7. Retrieving a Subset of Columns:
 * $users = User::select('name', 'email')->get();
 *
 * 8. Using where Clauses:
 * $users = User::where('status', 'active')->get();
 *
 * 9. Using orWhere Clauses:
 * $users = User::where('status', 'active')->orWhere('role', 'admin')->get();
 *
 * 10. Using whereBetween Clause:
 * $users = User::whereBetween('age', [18, 30])->get();
 *
 * 11. Using whereIn Clause:
 * $users = User::whereIn('id', [1, 2, 3])->get();
 *
 * 12. Using orderBy Clause:
 * $users = User::orderBy('name', 'asc')->get();
 *
 * 13. Using limit and offset:
 * $users = User::limit(10)->offset(5)->get();
 *
 * 14. Aggregates: count, max, min, avg, and sum:
 * $userCount = User::count();
 * $maxAge = User::max('age');
 * $minAge = User::min('age');
 * $averageAge = User::avg('age');
 * $totalAge = User::sum('age');
 *
 * 15. Using Relationships:
 * One-to-Many:
 * $posts = User::find(1)->posts; // Assuming a User has many Posts
 * 
 * Many-to-Many:
 * $roles = User::find(1)->roles; // Assuming a User belongs to many Roles
 * 
 * Eager Loading:
 * $users = User::with('posts')->get(); // Load users with their posts
 *
 * 16. Chunking Results:
 * User::chunk(100, function ($users) {
 *     foreach ($users as $user) {
 *         // Process each user
 *     }
 * });
 *
 * 17. Using pluck to Retrieve Lists of Column Values:
 * $emails = User::pluck('email');
 *
 * 18. Using exists to Check for Existence:
 * $exists = User::where('email', 'example@example.com')->exists();
 *
 * 19. Using firstOrCreate:
 * $user = User::firstOrCreate(['email' => 'john@example.com'], ['name' => 'John Doe', 'password' => bcrypt('password')]);
 *
 * 20. Using updateOrCreate:
 * $user = User::updateOrCreate(['email' => 'john@example.com'], ['name' => 'John Doe', 'password' => bcrypt('password')]);
 */

?>
