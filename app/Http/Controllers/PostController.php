<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPostEmail;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{

    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }


    public function updatePost(Post $post, Request $request) {
        //validate request
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        //strip down incoming tags
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        //actually update database
        $post->update($incomingFields);

        //redirect user back to the previous url
        return back()->with('success', 'Post successfully updated.');
    }

    public function showEditForm(Post $post) {
        return view('edit-post', ['post' => $post]);
    }

    public function delete(Post $post) {
        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted.');
    }

    //use type hinting so laravel can automatically lookup the correct post
    //second arg on return is the data we're passing to the route
    public function viewSinglePost (Post $post) {
        //check who is the author and only allow author see edit and delete buttons


        //add markdown support to the body of the post
        $post['body'] = Str::markdown($post->body);;
        return view('single-post', ['post' => $post, 'avatar' => auth()->user()->avatar]);
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

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name' => auth()->user()->username,
            'title' => $newPost->title,
        ]));



        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created');
    }

    //store new post using API
    public function storeNewPostApi(Request $request) {
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

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name' => auth()->user()->username,
            'title' => $newPost->title,
        ]));

        return $newPost->id;
    }

    public function showCreateForm () {
        return view('create-post');
    }
}
