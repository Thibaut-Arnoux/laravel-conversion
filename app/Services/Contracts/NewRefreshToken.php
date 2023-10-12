<?php

namespace App\Services\Contracts;

use App\Models\PersonalRefreshToken;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * @implements Arrayable<string, string|PersonalRefreshToken>
 */
class NewRefreshToken implements Arrayable, Jsonable
{
    /**
     * Create a new access token result.
     *
     * @return void
     */
    public function __construct(
        public PersonalRefreshToken $refreshToken,
        public string $plainTextToken
    ) {
    }

    /**
     * Get the instance as an array.
     *
     * @return array{refreshToken: PersonalRefreshToken, plainTextToken: string}
     */
    public function toArray(): array
    {
        return [
            'refreshToken' => $this->refreshToken,
            'plainTextToken' => $this->plainTextToken,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return (string) json_encode($this->toArray(), $options);
    }
}
