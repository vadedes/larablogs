<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user){
        //you cannot follow yourself
        if($user->id == auth()->user()->id){
           return back()->with('failure', 'You cannot follow yourself.'); 
        }

        //you cannot follow someone you're already following
        //make sure the arrays inside the main array of conditions are separated
        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        
        if($existCheck) {
            return back()->with('failure', 'You are already following that user.');
        }

        //create a new follow in the database
        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        //if all checks pass, redirect user back to previous url
        return back()->with('success', 'User successfully followed.');
    }

    public function removeFollow(User $user){
        Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->delete();
        return back()->with('success', 'User successfully unfollowed.');
    }
}

