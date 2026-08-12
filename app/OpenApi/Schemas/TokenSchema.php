<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * The payloads returned by the authentication endpoints.
 */
#[OA\Schema(
    schema: 'TokenResponse',
    properties: [
        new OA\Property(property: 'access_token', type: 'string', example: '1|zXKq8ItBhNQ0kZ2xJ7rGdM3sVaYwOe5UpLnCf4Th'),
        new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', description: 'Lifetime in seconds, or null when the token never expires', nullable: true, example: 3600),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AuthenticatedUser',
    properties: [
        new OA\Property(property: 'data', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Test User'),
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
        ], type: 'object'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Successfully logged out.'),
    ],
    type: 'object',
)]
final class TokenSchema
{
    //
}
