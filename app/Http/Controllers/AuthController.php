<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user authentication and issue a token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken('API Token')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid Credentials'], 401);
    }

    /**
     * Show message to user to get new token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unauthenticated()
    {
        return response()->json(['message' => 'Authentication failed! please send credintials again to get new token'], 401);
    }

    /**
     * Show message to user if he is not authorized.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function unauthorized()
    {
        return response()->json(['message' => 'You are unauthorized for this action'], 403);
    }
}