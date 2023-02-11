<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:3000'
        ]);

        //generate a random file name for the image
        //access the logged in user
        $user = auth()->user();

        //store it in a variable
        $filename = $user->id . '-' . uniqid() . '.jpg';

        //use the package intervention/image
        //store data first
        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        //store the data
        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;

        //update database
        $user->avatar = $filename; //name of avatar img
        $user->save();

        //delete old avatars to save space in storage
        //store old avatar in variable first before the new avatar is saved (e.g. $oldAvatar)
        if( $oldAvatar != '/fallback-avatar.jpg') {
            Storage::delete(str_replace('/storage/', 'public/', $oldAvatar));
        }

        //redirect user back to manage avatar screen
        return back()->with('success', 'Congrats on the new avatar.');
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

// Profile Posts page Methods Start
    private function getSharedData($user) {
        $currentlyFollowing = 0;

        if (auth()->check()) {
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        }

        View::share('sharedData', ['currentlyFollowing' => $currentlyFollowing, 'avatar' => $user->avatar, 'username' => $user->username, 'postCount' => $user->posts()->count(), 'followerCount' => $user->followers()->count(), 'followingCount' => $user->followingTheseUsers()->count()]);
    }


    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user) {
        return response()->json([
            'theHTML' => view('profile-posts-only', ['posts' => $user->posts()->latest()->get()])->render(),
            'docTitle' => $user->username . "'s Profile."
        ]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowersRaw(User $user) {
        return response()->json([
            'theHTML' => view('profile-followers-only', ['followers' => $user->followers()->latest()->get()])->render(),
            'docTitle' => $user->username . "'s Followers."
        ]);
    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user) {
        return response()->json([
            'theHTML' => view('profile-following-only', ['following' => $user->followingTheseUsers()->latest()->get()])->render(),
            'docTitle' => 'Who ' . $user->username . " Follows."
        ]);
    }
// Profile Posts page Methods End Here


    // public function profile(User $user) {

    //     //condition to check if the current user is following another user
    //     //if true, then don't show follow button anymore for that user
    //     $currentlyFollowing = 0;

    //     if(auth()->check()){
    //         $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();
    //     }

    //     //this line of code pulls all posts related to the user
    //     //its only possible if we define the relationship of the user to the posts
    //     //a user can hasMany posts, once a relationship has been set in the model,
    //     //we will then have the ability to pull all posts of the user using below code
    //     // return $user->posts()->get();
    //     return view('profile-posts', [
    //         'currentlyFollowing' => $currentlyFollowing,
    //         'avatar' => $user->avatar,
    //         'username' => $user->username,
    //         'posts'=> $user->posts()->latest()->get(),
    //         'postCount' => $user->posts()->count()
    //     ]);
    // }


    // public function profileFollowers(User $user) {

    //     $currentlyFollowing = 0;

    //     if(auth()->check()){
    //         $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();
    //     }

    //     return view('profile-followers', [
    //         'currentlyFollowing' => $currentlyFollowing,
    //         'avatar' => $user->avatar,
    //         'username' => $user->username,
    //         'posts'=> $user->posts()->latest()->get(),
    //         'postCount' => $user->posts()->count()
    //     ]);
    // }

    // public function profileFollowing(User $user) {

    //     $currentlyFollowing = 0;

    //     if(auth()->check()){
    //         $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();
    //     }

    //     return view('profile-following', [
    //         'currentlyFollowing' => $currentlyFollowing,
    //         'avatar' => $user->avatar,
    //         'username' => $user->username,
    //         'posts'=> $user->posts()->latest()->get(),
    //         'postCount' => $user->posts()->count()
    //     ]);
    // }



    public function logout() {
        event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'logout']));
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

    public function showCorrectHomepage() {
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(6)]);
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
            event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'login']));
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
