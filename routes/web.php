<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admins-only', function(){
    return 'you cannot view this page';
})->middleware('can:visit:AdminPages');

// User related routes
Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');
Route::get('/manage-avatar',[UserController::class,'showAvatarForm'])->middleware('mustbeloggedin');
Route::post('/manage-avatar',[UserController::class,'storeAvatar'])->middleware('mustbeloggedin');

//follow related routes
Route::post('/create-follow/{user:username}',[FollowController::class,'createfollow'])->middleware('mustbeloggedin');
Route::post('/remove-follow/{user:username}',[FollowController::class,'removefollow'])->middleware('mustbeloggedin');

// Blog post related routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('mustbeloggedin');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('mustbeloggedin');
Route::get('/post/{post}', [PostController::class, 'viewSinglePost']);
Route::delete('/post/{post}',[PostController::class,'delete']);

// profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);
