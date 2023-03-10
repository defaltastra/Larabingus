<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function storeAvatar(Request $request)
    {
        $request->validate([
            "avatar" => "required|image|max:3696",
        ]);
        $user = auth()->user();
        $filename = $user->id . "-" . uniqid() . ".jpg";
        $img = Image::make($request->file("avatar"))
            ->fit(120)
            ->encode("jpg");
        Storage::put("public/avatars/" . $filename, $img);

        $oldavatar = $user->avatar;
        $user->avatar = $filename;
        $user->save();

        if ($oldavatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/", "public/", $oldavatar));
        }
        return back()->with("success", "Avatar changed");
    }
    public function showAvatarForm()
    {
        return view("avatar-form");
    }
    private function getSharedDate($user)
    {
        $currentlyFollowing = 0;
        if (auth()->check()) {
            $currentlyFollowing = Follow::where(
                [["user_id", "=", auth()->user()->id]],
                ["followeduser", "=", $user->id]
            )->count();
        }
        View::share("sharedData", [
            "currentlyFollowing" => $currentlyFollowing,
            "avatar" => $user->avatar,
            "username" => $user->username,
            "postscount" => $user->posts()->count(), 'followerCount' => $user->followers()->count(), 'followingCount' => $user->followingTheseUsers()->count()
        ]);
    }
    public function profile(User $user)
    {
        $this->getSharedDate($user);
        return view("profile-posts", [
            "posts" => $user
                ->posts()
                ->latest()
                ->get(),
        ]);
    }
    public function profileFollowers(User $user)
    {
        $this->getSharedDate($user);

        return view("profile-followers", [
            "followers" => $user
                ->followers()
                ->latest()
                ->get(),
        ]);
    }
    public function profileFollowing(User $user)
    {
        $this->getSharedDate($user);

        return view("profile-following", [
            "following" => $user
                ->followingTheseUsers()
                ->latest()
                ->get(),
        ]);
    }
    public function logout()
    {
        auth()->logout();
        return redirect("/")->with("success", "You are now logged out.");
    }

    public function showCorrectHomepage()
    {
        if (auth()->check()) {
            return view("homepage-feed");
        } else {
            return view("homepage");
        }
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            "loginusername" => "required",
            "loginpassword" => "required",
        ]);

        if (
            auth()->attempt([
                "username" => $incomingFields["loginusername"],
                "password" => $incomingFields["loginpassword"],
            ])
        ) {
            $request->session()->regenerate();
            return redirect("/")->with(
                "success",
                "You have successfully logged in."
            );
        } else {
            return redirect("/")->with("failure", "Invalid login.");
        }
    }

    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            "username" => [
                "required",
                "min:3",
                "max:20",
                Rule::unique("users", "username"),
            ],
            "email" => ["required", "email", Rule::unique("users", "email")],
            "password" => ["required", "min:8", "confirmed"],
        ]);

        $incomingFields["password"] = bcrypt($incomingFields["password"]);

        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect("/")->with(
            "success",
            "Thank you for creating an account."
        );
    }
}
