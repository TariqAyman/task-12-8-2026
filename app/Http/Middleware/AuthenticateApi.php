<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates API requests using either HTTP Basic Authentication or a
 * Sanctum personal access token, so a single protected route group satisfies
 * both schemes.
 */
class AuthenticateApi
{
    /**
     * The guard backing Sanctum personal access tokens.
     */
    protected const TOKEN_GUARD = 'api';

    /**
     * The guard used to verify HTTP Basic credentials.
     */
    protected const BASIC_GUARD = 'web';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isBearerRequest($request)) {
            return $this->authenticateWithToken($request, $next);
        }

        return $this->authenticateWithBasic($request, $next);
    }

    /**
     * Determine whether the request carries a bearer token.
     */
    protected function isBearerRequest(Request $request): bool
    {
        return $request->bearerToken() !== null;
    }

    /**
     * Authenticate the request using the Sanctum token guard.
     *
     * @param  Closure(Request): Response  $next
     */
    protected function authenticateWithToken(Request $request, Closure $next): Response
    {
        if (! Auth::guard(self::TOKEN_GUARD)->check()) {
            return $this->unauthorized('The provided token is invalid or has expired.');
        }

        Auth::shouldUse(self::TOKEN_GUARD);

        return $next($request);
    }

    /**
     * Authenticate the request using stateless HTTP Basic Authentication.
     *
     * @param  Closure(Request): Response  $next
     */
    protected function authenticateWithBasic(Request $request, Closure $next): Response
    {
        if ($request->getUser() === null) {
            return $this->unauthorized('Authentication credentials were not provided.');
        }

        $authenticated = Auth::guard(self::BASIC_GUARD)->once([
            'email' => $request->getUser(),
            'password' => (string) $request->getPassword(),
        ]);

        if (! $authenticated) {
            return $this->unauthorized('The provided credentials are incorrect.');
        }

        return $next($request);
    }

    /**
     * Build a JSON unauthorized response that still advertises the Basic scheme.
     */
    protected function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED, [
            'WWW-Authenticate' => 'Basic realm="API"',
        ]);
    }
}
