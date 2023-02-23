<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user){
        // you cant follow yourself
        if ($user->id == auth()->user()->id){
            return back()->with('failure','you cannot follow yourself');
        }
        // you can't follow someone you already followed
        $existChek = Follow::where([['user_id', '=',auth()->user()->id]],['followeduser','=',$user->id])->count();
        if ($existChek){
            return back()->with('failure','you already follow that user');
        }
        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser= $user->id;
        $newFollow->save();
        return back()->with('success','user successfuly followed');
    }
    public function removefollow(User $user){
        Follow::where([['user_id','=',auth()->user()->id]],[['followeduser','=',$user->id]])->delete();
       return back()->with('success','User Unfollowed');
    }
}
