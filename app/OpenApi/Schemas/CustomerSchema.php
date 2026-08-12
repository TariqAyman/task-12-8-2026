<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * The customer representations exchanged with the API.
 */
#[OA\Schema(
    schema: 'Customer',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+1 555 0100'),
        new OA\Property(property: 'company', type: 'string', nullable: true, example: 'Doe Consulting'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: '1 Market Street'),
        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Berlin'),
        new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Germany'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'services_count', type: 'integer', example: 3),
        new OA\Property(property: 'services', type: 'array', items: new OA\Items(ref: '#/components/schemas/Service')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CustomerRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Jane Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+1 555 0100'),
        new OA\Property(property: 'company', type: 'string', nullable: true, example: 'Doe Consulting'),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Berlin'),
        new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Germany'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CustomerCollection',
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Customer')),
        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CustomerEnvelope',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Customer'),
    ],
    type: 'object',
)]
final class CustomerSchema
{
    //
}
