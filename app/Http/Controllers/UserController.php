<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function storeAvatar(Request $request) {
        $request->file('avatar')->store('/public/avatars');
        return 'hey';
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

    public function profile(User $user) {

        //this line of code pulls all posts related to the user
        //its only possible if we define the relationship of the user to the posts
        //a user can hasMany posts, once a relationship has been set in the model,
        //we will then have the ability to pull all posts of the user using below code
        // return $user->posts()->get();
        return view('profile-posts', [
            'username' => $user->username, 
            'posts'=> $user->posts()->latest()->get(),
            'postCount' => $user->posts()->count()
        ]);
    }

    public function logout() {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

    public function showCorrectHomepage() {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('homepage');
        }
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in.');
        } else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }

    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success', 'Thank you for creating an account.');
    }
}
