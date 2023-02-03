<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function storeNewPost(Request $request) {
        // validate the incoming data first
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        //strip all incoming field data of tags
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        //add the author of the post to the array of incoming data
        //from the auth object
        $incomingFields['user_id'] = auth()->id();

        //saving to db logic here
        Post::create($incomingFields);
        return redirect('/');
    }

    public function showCreateForm () {
        return view('create-post');
    }
}
