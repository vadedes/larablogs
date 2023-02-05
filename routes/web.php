<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

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
    return 'Only admins are allowed to view this route';
})->middleware('can:visitAdminPages');

// User Related Routes
Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('MustBeLoggedIn');

// Blog Post Related Routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('MustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('MustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewSinglePost'])->middleware('MustBeLoggedIn');
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'updatePost'])->middleware('can:update,post');

//Profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('MustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('MustBeLoggedIn');