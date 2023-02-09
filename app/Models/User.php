<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Follow;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    //we get to filter what the incoming value of the incoming avatar should be
    //method to programmatically locate the avatar img source and
    //add a condition if an avatar is not present in the database use a default profile img
    protected function avatar(): Attribute {
        return Attribute::make(get: function($value){
            return $value ? '/storage/avatars/' . $value : '/fallback-avatar.jpg';
        });

    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    //This relationship between users and posts to show posts of users that the user currently follow
    //on users homepagefeed
    //has 6 args as follows:
    //arg 1. Begin with the model that want to end up with - blog posts from the users that current user follows
    //arg 2. Intermmediate table - table that has the data that we need to look up
    //arg 3. The foreign key of the intermmediate table
    //arg 4. The foreign key from the model that we're interested in - from Post model
    //arg 5. is the local key - we're working on the user model so user is what's local now
    //arg 6. is the local key on our intermmediate table
    public function feedPosts() {
        return $this->hasManyThrough(Post::class, Follow::class, 'user_id', 'user_id', 'id', 'followeduser');
    }

    //This relationship means a user has many followers -> followeduser in Follow Table
    public function followers(){
        return $this->hasMany(Follow::class, 'followeduser');
    }

    //this user is following many users -> user_id in Follow table
    public function followingTheseUsers(){
        return $this->hasMany(Follow::class, 'user_id');
    }

    //A user has many posts
    public function posts() {
        return $this->hasMany(Post::class, 'user_id');
    }
}
