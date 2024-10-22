<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            /**
             * Email
             * 
             * @example Shandia@example.com
             */
            'email' => 'required|string|email',
            /**
             * Password
             * 
             * @example Admin123
             */
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'invalid email or password'  
        ], 422);
    }

    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'success' => true,
        'data' => [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ],
        'message' => 'Successfully logged in' 
    ]);
    }

}
