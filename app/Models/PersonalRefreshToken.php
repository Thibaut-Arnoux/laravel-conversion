<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalRefreshToken extends Model
{
    use HasFactory;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'token',
        'expires_at',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * Get the access token that the refresh token belongs to.
     *
     * @return BelongsTo<PersonalAccessToken, self>
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    /**
     * Get the tokenable model that the refresh token belongs to.
     *
     * @return MorphTo<Model, self>
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Find the token instance matching the given token.
     */
    public static function findToken(string $token): ?self
    {
        if (strpos($token, '|') === false) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }

        return null;
    }
}
