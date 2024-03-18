<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * insert new user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:6|max:255',
            'role' => 'required|string|in:user,manager'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            // Create new user instance
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->role = $request->input('role');

            // Save the user
            $user->save();

            // return success message with user instance
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            // return any error
            return response()->json(['errors' => $e], 500);
        }
    }
}
