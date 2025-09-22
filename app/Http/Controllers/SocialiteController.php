<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
//use http\Env\Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;




class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }
    public function handleGoogleCallback()
    {
        try {
            $user=Socialite::driver('google')->user();
//            dd($user);
            $finduser = User::where('social_id',$user->id)->first();
            if($finduser){
                Auth::login($finduser);
                return response()->json($finduser);
            }else{
                $newUser=User::create([
                    'name'=>$user->name,
                    'nickname'=>$user->nickname,
                    'avatar'=>$user->getAvatar(),
                    'email'=>$user->email,
                    'social_id'=>$user->id,
                    'social_type'=>'google',
                    'token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                ]);
                Auth::login($newUser);
                return response()->json($newUser);
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }
    }





    public function loginWithGoogleToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $accessToken = $request->input('access_token');

            $googleUser = Socialite::driver('google')->stateless()->userFromToken($accessToken);

            $finduser = User::where('social_id', $googleUser->id)->first();

            if ($finduser) {
                Auth::login($finduser);
                return response()->json($finduser, 200);
            } else {
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'nickname' => $googleUser->nickname ?? null,
                    'avatar' => $googleUser->getAvatar(),
                    'email' => $googleUser->email,
                    'social_id' => $googleUser->id,
                    'social_type' => 'google',
                    'token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken ?? null,
                ]);

                Auth::login($newUser);
                return response()->json($newUser, 201);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function handleGithubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
//            dd($token);
            $finduser = User::where('social_id',$githubUser->id)->first();
            if($finduser){
                Auth::login($finduser);
                return response()->json($finduser);
            }else{
                $newUser=User::create([
                    'name' => $githubUser->name,
                    'nickname'=>$githubUser->nickname,
                    'avatar'=>$githubUser->getAvatar(),
                    'email' => $githubUser->email,
                    'social_id'=> $githubUser->id,
                    'social_type'=> 'github',
                    'token' => $githubUser->token,
                    'refresh_token' => $githubUser->refreshToken,
                ]);
                Auth::login($newUser);
                return response()->json($newUser);
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }
    }
}
