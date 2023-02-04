<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //use type hinting so laravel can automatically lookup the correct post
    //second arg on return is the data we're passing to the route
    public function viewSinglePost (Post $post) {
        //add markdown support to the body of the post
        $post['body'] = Str::markdown($post->body);;
        return view('single-post', ['post' => $post]);
    }


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
        $newPost = Post::create($incomingFields);
        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created');
    }

    public function showCreateForm () {
        return view('create-post');
    }
}
