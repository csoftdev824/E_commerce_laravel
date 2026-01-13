<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        // Get the authenticated user - Auth::attempt() in authenticate() sets the user
        $user = Auth::user();
        
        // Fallback: if Auth::user() returns null, get user by email
        if (!$user) {
            $user = \App\Models\User::where('email', $request->email)->first();
        }

        if (!$user) {
            return response()->json([
                'message' => 'Authentication failed.',
            ], 401);
        }

        // Create API token for the user
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
