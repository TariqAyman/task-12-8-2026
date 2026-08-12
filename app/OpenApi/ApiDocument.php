<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * The root of the OpenAPI document: general information, the server, the
 * accepted security schemes, the tags and the responses shared by every
 * endpoint.
 *
 * Every annotation of the API lives under `App\OpenApi` so the controllers stay
 * free of documentation noise.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'Customer & Service API',
    description: <<<'DESCRIPTION'
        A REST API exposing CRUD operations over customers and the services they subscribe to.

        Every endpoint below is protected. Two authentication schemes are accepted on the
        same routes:

        - **basicAuth** – HTTP Basic Authentication using a registered user's email and password.
        - **bearerAuth** – a Sanctum personal access token obtained from `POST /api/v1/auth/login`.

        Use the *Authorize* button to supply either one.
        DESCRIPTION,
    contact: new OA\Contact(name: 'API Support', email: 'support@example.com'),
)]
#[OA\Server(url: '/api', description: 'Current host')]
#[OA\SecurityScheme(
    securityScheme: 'basicAuth',
    type: 'http',
    description: 'HTTP Basic Authentication with a registered user email and password.',
    scheme: 'basic',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Sanctum personal access token issued by the login endpoint.',
    scheme: 'bearer',
)]
#[OA\Tag(name: 'Authentication', description: 'Obtain and manage personal access tokens.')]
#[OA\Tag(name: 'Customers', description: 'Manage customers.')]
#[OA\Tag(name: 'Services', description: 'Manage the services belonging to customers.')]
#[OA\Response(
    response: 'Unauthorized',
    description: 'Authentication credentials were missing or invalid.',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Authentication credentials were not provided.'),
    ]),
)]
#[OA\Response(
    response: 'NotFound',
    description: 'The requested record does not exist.',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'No query results for model.'),
    ]),
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'The payload failed validation.',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', type: 'object', additionalProperties: new OA\AdditionalProperties(
            type: 'array',
            items: new OA\Items(type: 'string'),
        )),
    ]),
)]
final class ApiDocument
{
    //
}
