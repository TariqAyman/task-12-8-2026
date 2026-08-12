<?php

namespace App\Support\Auth;

use Laravel\Sanctum\NewAccessToken;

/**
 * A freshly issued personal access token, decoupled from Sanctum's own object.
 */
final readonly class IssuedToken
{
    /**
     * Create a new issued token.
     *
     * @param  int|null  $expiresIn  the lifetime in seconds, or null when the token never expires
     */
    public function __construct(
        public string $plainTextToken,
        public ?int $expiresIn,
    ) {}

    /**
     * Build the issued token from the token Sanctum just created.
     */
    public static function fromSanctum(NewAccessToken $token): self
    {
        $expiresAt = $token->accessToken->expires_at;

        if ($expiresAt !== null) {
            return new self($token->plainTextToken, (int) max(0, round(now()->diffInSeconds($expiresAt))));
        }

        $minutes = config('sanctum.expiration');

        return new self(
            $token->plainTextToken,
            $minutes === null ? null : (int) $minutes * 60,
        );
    }
}
