<?php

namespace App\OpenApi\Paths;

use App\Http\Controllers\Api\V1\CustomerController;
use App\Support\Queries\CustomerQuery;
use OpenApi\Attributes as OA;

/**
 * The documentation of the endpoints served by
 * {@see CustomerController}.
 */
final class CustomerPaths
{
    /**
     * @see CustomerController::index()
     */
    #[OA\Get(
        path: '/v1/customers',
        summary: 'View all customers',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Filter by name, email or company', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: CustomerQuery::SORTABLE_COLUMNS)),
            new OA\Parameter(ref: '#/components/parameters/SortDirection'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A paginated list of customers', content: new OA\JsonContent(ref: '#/components/schemas/CustomerCollection')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    public function index(): void {}

    /**
     * @see CustomerController::store()
     */
    #[OA\Post(
        path: '/v1/customers',
        summary: 'Create a customer',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CustomerRequest')),
        responses: [
            new OA\Response(response: 201, description: 'The created customer', content: new OA\JsonContent(ref: '#/components/schemas/CustomerEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function store(): void {}

    /**
     * @see CustomerController::show()
     */
    #[OA\Get(
        path: '/v1/customers/{customer}',
        summary: 'View a customer',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'The requested customer', content: new OA\JsonContent(ref: '#/components/schemas/CustomerEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function show(): void {}

    /**
     * @see CustomerController::update()
     */
    #[OA\Put(
        path: '/v1/customers/{customer}',
        summary: 'Update a customer',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CustomerRequest')),
        responses: [
            new OA\Response(response: 200, description: 'The updated customer', content: new OA\JsonContent(ref: '#/components/schemas/CustomerEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    #[OA\Patch(
        path: '/v1/customers/{customer}',
        summary: 'Partially update a customer',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CustomerRequest')),
        responses: [
            new OA\Response(response: 200, description: 'The updated customer', content: new OA\JsonContent(ref: '#/components/schemas/CustomerEnvelope')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function update(): void {}

    /**
     * @see CustomerController::destroy()
     */
    #[OA\Delete(
        path: '/v1/customers/{customer}',
        summary: 'Delete a customer',
        description: 'Soft deletes the customer together with every service that belongs to it.',
        security: [['basicAuth' => []], ['bearerAuth' => []]],
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'The customer was deleted', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Customer deleted successfully.'),
            ])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function destroy(): void {}
}
