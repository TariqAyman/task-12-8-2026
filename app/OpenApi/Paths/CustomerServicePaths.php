<?php

namespace App\OpenApi\Paths;

use App\Http\Controllers\Api\V1\CustomerServiceController;
use App\Support\Queries\ServiceQuery;
use OpenApi\Attributes as OA;

/**
 * The documentation of the endpoints served by
 * {@see CustomerServiceController}.
 */
final class CustomerServicePaths
{
    /**
     * @see CustomerServiceController::index()
     */
    #[OA\Get(
        path: '/v1/customers/{customer}/services',
        summary: 'View services of a customer',
        description: 'Listing responses are cached and invalidated automatically whenever a service changes.',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'search', in: 'query', description: 'Filter by name or description', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'active', 'suspended', 'cancelled'])),
            new OA\Parameter(name: 'billing_cycle', in: 'query', schema: new OA\Schema(type: 'string', enum: ['one_time', 'monthly', 'quarterly', 'yearly'])),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ServiceQuery::SORTABLE_COLUMNS)),
            new OA\Parameter(ref: '#/components/parameters/SortDirection'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A paginated list of the customer services', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCollection')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function index(): void {}

    /**
     * @see CustomerServiceController::store()
     */
    #[OA\Post(
        path: '/v1/customers/{customer}/services',
        summary: 'Create a service for a customer',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceRequest')),
        responses: [
            new OA\Response(response: 201, description: 'The created service', content: new OA\JsonContent(ref: '#/components/schemas/ServiceEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function store(): void {}
}
