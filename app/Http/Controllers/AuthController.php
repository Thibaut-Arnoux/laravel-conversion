<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetRequest;
use App\Http\Resources\UserResource;
use App\Models\PersonalRefreshToken;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Register a user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $name = $request->userAgent() ?? 'auth_token';
        $accessToken = $user->createToken($name);
        $refreshToken = $user->createRefreshToken($name, $accessToken->accessToken);

        return new JsonResponse(
            [
                'user' => UserResource::make($user),
                'tokens' => [
                    'access_token' => $accessToken->plainTextToken,
                    'token_type' => 'Bearer',
                    'refresh_token' => $refreshToken->plainTextToken,
                ],
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
        $accessToken = $user->createToken($name);
        $refreshToken = $user->createRefreshToken($name, $accessToken->accessToken);

        return new JsonResponse(
            [
                'user' => UserResource::make($user),
                'tokens' => [
                    'access_token' => $accessToken->plainTextToken,
                    'token_type' => 'Bearer',
                    'refresh_token' => $refreshToken->plainTextToken,
                ],
            ],
        );
    }

    /**
     * Log out a user
     */
    public function logout(): JsonResponse
    {
        Auth::user()->refreshTokens()->delete();
        Auth::user()->tokens()->delete();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT,
        );
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        // injected from middleware
        /** @var PersonalRefreshToken $refreshToken */
        $refreshToken = $request->refresh_token;
        /** @var User $user */
        $user = $refreshToken->tokenable;

        // revoke token
        $refreshToken->delete();
        $refreshToken->accessToken->delete();

        // create new token
        $name = $request->userAgent() ?? 'auth_token';
        $accessToken = $user->createToken($name);
        $refreshToken = $user->createRefreshToken($name, $accessToken->accessToken);

        return new JsonResponse(
            [
                'access_token' => $accessToken->plainTextToken,
                'token_type' => 'Bearer',
                'refresh_token' => $refreshToken->plainTextToken,
            ],
        );
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(Request $request): JsonResponse
    {
        return UserResource::make($request->user())
            ->toResponse($request);
    }

    /**
     * Update user password
     */
    public function updatePassword(PasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT,
        );
    }

    /**
     * Forgot password
     */
    public function forgotPassword(ForgotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // set local to fr to have default mailing in french
        App::setLocale('fr');

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => $status], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => $status,
            ]);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(ResetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $reset = [
            'token' => $validated['token'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $status = Password::reset(
            $reset,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return new JsonResponse([
                'message' => $status,
            ]);
        } else {
            throw ValidationException::withMessages([
                'email' => $status,
            ]);
        }
    }
}
