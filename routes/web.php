<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

//admins only route
Route::get('admin-dashboard', function(){
    return 'Welcome! Only admins are allowed to view this route';
})->middleware('can:visitAdminPages');

// User Related Routes
Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('MustBeLoggedIn');

//Follow related routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('MustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware('MustBeLoggedIn');

// Blog Post Related Routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('MustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('MustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewSinglePost'])->middleware('MustBeLoggedIn');
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'updatePost'])->middleware('can:update,post');
Route::get('/search/{term}', [PostController::class, 'search']);

//Profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('MustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('MustBeLoggedIn');
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);
//Raw data of Profile routes
Route::get('/profile/{user:username}/raw', [UserController::class, 'profileRaw']);
Route::get('/profile/{user:username}/followers/raw', [UserController::class, 'profileFollowersRaw']);
Route::get('/profile/{user:username}/following/raw', [UserController::class, 'profileFollowingRaw']);

//Chat routes
Route::post('/send-chat-message', function (Request $request) {
    $formFields = $request->validate([
      'textvalue' => 'required'
    ]);

    if (!trim(strip_tags($formFields['textvalue']))) {
      return response()->noContent();
    }

    broadcast(new ChatMessage(['username' =>auth()->user()->username, 'textvalue' => strip_tags($request->textvalue), 'avatar' => auth()->user()->avatar]))->toOthers();
    return response()->noContent();

  })->middleware('MustBeLoggedIn');
