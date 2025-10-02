<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $userData)
    {
        // Create user
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Create token for API authentication
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function login(array $credentials)
    {
        // Attempt to authenticate
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
        
        // Revoke all previous tokens (optional - for security)
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout()
    {
        // Revoke current user's token
        if (Auth::user()) {
            Auth::user()->currentAccessToken()->delete();
            return true;
        }
        return false;
    }

    public function getUser()
    {
        return Auth::user();
    }
}

