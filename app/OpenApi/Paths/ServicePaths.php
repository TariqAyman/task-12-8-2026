<?php

namespace App\OpenApi\Paths;

use App\Http\Controllers\Api\V1\ServiceController;
use App\Support\Queries\ServiceQuery;
use OpenApi\Attributes as OA;

/**
 * The documentation of the endpoints served by
 * {@see ServiceController}.
 */
final class ServicePaths
{
    /**
     * @see ServiceController::index()
     */
    #[OA\Get(
        path: '/v1/services',
        summary: 'View all services',
        description: 'Listing responses are cached and invalidated automatically whenever a service changes.',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'customer_id', in: 'query', description: 'Restrict the listing to a single customer', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', description: 'Filter by name or description', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'active', 'suspended', 'cancelled'])),
            new OA\Parameter(name: 'billing_cycle', in: 'query', schema: new OA\Schema(type: 'string', enum: ['one_time', 'monthly', 'quarterly', 'yearly'])),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ServiceQuery::SORTABLE_COLUMNS)),
            new OA\Parameter(ref: '#/components/parameters/SortDirection'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A paginated list of services', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCollection')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    public function index(): void {}

    /**
     * @see ServiceController::show()
     */
    #[OA\Get(
        path: '/v1/services/{service}',
        summary: 'View a service',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'The requested service including its customer', content: new OA\JsonContent(ref: '#/components/schemas/ServiceEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function show(): void {}

    /**
     * @see ServiceController::update()
     */
    #[OA\Put(
        path: '/v1/services/{service}',
        summary: 'Update a service',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'The updated service', content: new OA\JsonContent(ref: '#/components/schemas/ServiceEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    #[OA\Patch(
        path: '/v1/services/{service}',
        summary: 'Partially update a service',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'The updated service', content: new OA\JsonContent(ref: '#/components/schemas/ServiceEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function update(): void {}

    /**
     * @see ServiceController::destroy()
     */
    #[OA\Delete(
        path: '/v1/services/{service}',
        summary: 'Delete a service',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'The service was deleted', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Service deleted successfully.'),
            ])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function destroy(): void {}
}
