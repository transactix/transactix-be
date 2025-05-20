<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the user exists
        $user = User::findByEmail($request->email);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Generate a new reset token
        $token = Str::random(60);
        
        // Store the token in the password_reset_tokens table
        \App\Facades\Supabase::delete('password_reset_tokens', ['email' => $request->email], true);
        \App\Facades\Supabase::insert('password_reset_tokens', [
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now()->toDateTimeString()
        ], true);

        // Send the reset link email
        // In a real application, you would send an email with the reset link
        // For this example, we'll just return the token in the response
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent',
            'data' => [
                'token' => $token,
                'email' => $request->email
            ]
        ]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the token
        $resetRecords = \App\Facades\Supabase::query('password_reset_tokens', [
            'where' => ['email' => $request->email],
            'limit' => 1
        ], true);

        if (empty($resetRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 400);
        }

        $resetRecord = $resetRecords[0];
        
        // Check if token is valid
        if (!Hash::check($request->token, $resetRecord['token'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 400);
        }

        // Check if token is expired (1 hour)
        $createdAt = \Carbon\Carbon::parse($resetRecord['created_at']);
        if ($createdAt->diffInMinutes(now()) > 60) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 400);
        }

        // Find the user
        $user = User::findByEmail($request->email);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Update the user's password
        $user->update([
            'password' => $request->password
        ]);

        // Delete the token
        \App\Facades\Supabase::delete('password_reset_tokens', ['email' => $request->email], true);

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}
