<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\TokenResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AuthService $auth) {}

    /**
     * Issue a personal access token for the given credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->auth->attempt($request->credentials());

        if ($token === null) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return TokenResource::make($token)->response();
    }

    /**
     * Get the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()?->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Exchange the current token for a freshly issued one.
     */
    public function refresh(Request $request): JsonResponse
    {
        $token = $this->auth->refresh($this->user($request), $request->bearerToken());

        if ($token === null) {
            return response()->json([
                'message' => 'The provided token cannot be refreshed.',
            ], 401);
        }

        return TokenResource::make($token)->response();
    }

    /**
     * Revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        if (! $this->auth->revoke($this->user($request), $request->bearerToken())) {
            return response()->json([
                'message' => 'The provided token cannot be invalidated.',
            ], 401);
        }

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get the authenticated user the route middleware already resolved.
     */
    protected function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
