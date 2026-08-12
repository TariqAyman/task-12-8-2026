<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * The service representations exchanged with the API.
 */
#[OA\Schema(
    schema: 'Service',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'customer_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Managed Hosting'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 149.99),
        new OA\Property(property: 'currency', type: 'string', maxLength: 3, example: 'USD'),
        new OA\Property(property: 'billing_cycle', type: 'string', enum: ['one_time', 'monthly', 'quarterly', 'yearly'], example: 'monthly'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'suspended', 'cancelled'], example: 'active'),
        new OA\Property(property: 'annual_value', type: 'number', format: 'float', example: 1799.88),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date', nullable: true, example: '2026-01-01'),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date', nullable: true, example: '2026-12-31'),
        new OA\Property(property: 'customer', ref: '#/components/schemas/Customer', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ServiceRequest',
    required: ['name', 'price'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Managed Hosting'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 149.99),
        new OA\Property(property: 'currency', type: 'string', maxLength: 3, example: 'USD'),
        new OA\Property(property: 'billing_cycle', type: 'string', enum: ['one_time', 'monthly', 'quarterly', 'yearly'], example: 'monthly'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'suspended', 'cancelled'], example: 'active'),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date', nullable: true, example: '2026-01-01'),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date', nullable: true, example: '2026-12-31'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ServiceUpdateRequest',
    properties: [
        new OA\Property(property: 'customer_id', type: 'integer', description: 'Move the service to another customer.', example: 2),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Managed Hosting'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 199.0),
        new OA\Property(property: 'currency', type: 'string', maxLength: 3, example: 'USD'),
        new OA\Property(property: 'billing_cycle', type: 'string', enum: ['one_time', 'monthly', 'quarterly', 'yearly'], example: 'yearly'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'suspended', 'cancelled'], example: 'suspended'),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ServiceCollection',
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Service')),
        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ServiceEnvelope',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Service'),
    ],
    type: 'object',
)]
final class ServiceSchema
{
    //
}
