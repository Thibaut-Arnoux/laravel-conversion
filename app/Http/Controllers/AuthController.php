<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Register a user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $name = $request->userAgent() ?? 'auth_token';
        $token = $user->createToken($name)->plainTextToken;

        return new JsonResponse(
            [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            Response::HTTP_CREATED,
        );
    }

    /**
     * Log in a user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            throw new Exception('Failed authentication, please check your credentials.');
        }

        $user = Auth::user();
        $name = $request->userAgent() ?? 'auth_token';
        $token = $user->createToken($name)->plainTextToken;

        return new JsonResponse(
            [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        );
    }

    /**
     * Log out a user
     */
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT,
        );
    }
}
