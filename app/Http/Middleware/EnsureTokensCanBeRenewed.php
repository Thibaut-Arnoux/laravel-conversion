<?php

namespace App\Http\Middleware;

use App\Models\PersonalRefreshToken;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokensCanBeRenewed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessTokenFromRequest = $request->bearerToken() ?? '';
        $refreshTokenFromRequest = is_string($request->cookie('refresh_token'))
            ? (string) $request->cookie('refresh_token')
            : null;

        $accessToken = PersonalAccessToken::findToken($accessTokenFromRequest);
        $refreshToken = PersonalRefreshToken::findToken($refreshTokenFromRequest);

        if (! $this->isValidRefreshToken($refreshToken)
            || $accessToken === null
            || $refreshToken->accessToken->token !== $accessToken->token) {
            return response()->json([
                'message' => 'Invalid parameters to refresh token.',
            ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $request->merge([
            'refresh_token' => $refreshToken,
        ]);

        return $next($request);
    }

    protected function isValidRefreshToken(?PersonalRefreshToken $refreshToken): bool
    {
        if ($refreshToken === null) {
            return false;
        }

        $expiration = config('sanctum.expiration_refresh_token');

        return (! $expiration || $refreshToken->created_at->gt(now()->subMinutes($expiration)))
            && (! $refreshToken->expires_at || ! $refreshToken->expires_at->isPast());
    }
}
