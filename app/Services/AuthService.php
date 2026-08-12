<?php

namespace App\Services;

use App\Models\User;
use App\Support\Auth\IssuedToken;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Issues, rotates and revokes the personal access tokens backing the API.
 */
class AuthService
{
    /**
     * The name given to every token issued by the API.
     */
    public const TOKEN_NAME = 'api';

    /**
     * Exchange the given credentials for a personal access token.
     *
     * @param  array{email: string, password: string}  $credentials
     */
    public function attempt(array $credentials): ?IssuedToken
    {
        $user = $this->retrieveByCredentials($credentials);

        if (! $user instanceof User) {
            return null;
        }

        return $this->issue($user);
    }

    /**
     * Issue a new token for the given user.
     */
    public function issue(User $user): IssuedToken
    {
        return IssuedToken::fromSanctum($user->createToken(self::TOKEN_NAME));
    }

    /**
     * Rotate the token the request was authenticated with.
     *
     * Returns null when the request carries no token of its own, which is the
     * case for HTTP Basic Authentication.
     */
    public function refresh(User $user, ?string $plainTextToken): ?IssuedToken
    {
        if (! $this->revoke($user, $plainTextToken)) {
            return null;
        }

        return $this->issue($user);
    }

    /**
     * Revoke the given token, as long as it belongs to the given user.
     */
    public function revoke(User $user, ?string $plainTextToken): bool
    {
        $token = $plainTextToken === null
            ? null
            : PersonalAccessToken::findToken($plainTextToken);

        if ($token === null || ! $token->tokenable?->is($user)) {
            return false;
        }

        return (bool) $token->delete();
    }

    /**
     * Retrieve the user matching the given credentials.
     *
     * @param  array{email: string, password: string}  $credentials
     */
    protected function retrieveByCredentials(array $credentials): ?User
    {
        $provider = Auth::createUserProvider('users');

        if ($provider === null) {
            return null;
        }

        $user = $provider->retrieveByCredentials($credentials);

        if (! $user instanceof User || ! $provider->validateCredentials($user, $credentials)) {
            return null;
        }

        return $user;
    }
}
