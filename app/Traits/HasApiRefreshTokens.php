<?php

namespace App\Traits;

use App\Models\PersonalRefreshToken;
use App\Services\Contracts\NewRefreshToken;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\PersonalAccessToken;
use Str;

trait HasApiRefreshTokens
{
    /**
     * Get the refresh tokens that belong to model.
     *
     * @return MorphMany<PersonalRefreshToken>
     */
    public function refreshTokens(): MorphMany
    {
        return $this->morphMany(PersonalRefreshToken::class, 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     */
    public function createRefreshToken(string $name, PersonalAccessToken $accessToken, DateTimeInterface $expiresAt = null): NewRefreshToken
    {
        $plainTextToken = sprintf(
            '%s%s',
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );

        /** @var PersonalRefreshToken $token */
        $token = $this->refreshTokens()->make([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
        ]);
        $token->accessToken()->associate($accessToken);
        $token->save();

        return new NewRefreshToken($token, $token->getKey().'|'.$plainTextToken);
    }
}
