<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes(['email', 'profile']);

        return redirect()->away($client->createAuthUrl());
    }
    public function googleAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $code = $request->input('code');

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes(['email', 'profile']);

        $accessToken = $client->fetchAccessTokenWithAuthCode($code);

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to fetch access token'], 400);
        }

        $client->setAccessToken($accessToken);
        $oauth2Service = new Google_Service_Oauth2($client);
        $userInfo = $oauth2Service->userinfo->get();

        $email = $userInfo->getEmail();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = new User();
            $user->name = $userInfo->getName();
            $user->email = $email;
            $user->password = Hash::make(Str::random(32));
            $user->save();
        }

        $token = JWTAuth::fromUser($user);

        return redirect("http://localhost:3000?token=$token");
    }
}