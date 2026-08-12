<?php

namespace App\OpenApi\Paths;

use App\Http\Controllers\Api\V1\AuthController;
use OpenApi\Attributes as OA;

/**
 * The documentation of the endpoints served by
 * {@see AuthController}.
 */
final class AuthPaths
{
    /**
     * @see AuthController::login()
     */
    #[OA\Post(
        path: '/v1/auth/login',
        summary: 'Log in and receive a personal access token',
        description: 'Exchanges a registered user email and password for a Sanctum personal access token.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Token issued', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function login(): void {}

    /**
     * @see AuthController::me()
     */
    #[OA\Get(
        path: '/v1/auth/me',
        summary: 'Get the authenticated user',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'The authenticated user', content: new OA\JsonContent(ref: '#/components/schemas/AuthenticatedUser')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    public function me(): void {}

    /**
     * @see AuthController::refresh()
     */
    #[OA\Post(
        path: '/v1/auth/refresh',
        summary: 'Rotate the current personal access token',
        description: 'Revokes the token the request was made with and issues a new one. Requires a bearer token; Basic credentials cannot be refreshed.',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Token rotated', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    public function refresh(): void {}

    /**
     * @see AuthController::logout()
     */
    #[OA\Post(
        path: '/v1/auth/logout',
        summary: 'Revoke the current personal access token',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Token revoked', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    public function logout(): void {}
}
