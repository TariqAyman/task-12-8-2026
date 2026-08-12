<?php

namespace App\OpenApi\Parameters;

use App\Support\Queries\Sorting;
use OpenApi\Attributes as OA;

/**
 * The query parameters every paginated listing accepts.
 */
#[OA\Parameter(
    parameter: 'SortDirection',
    name: 'direction',
    in: 'query',
    description: 'The direction the listing is ordered in.',
    schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc']),
)]
#[OA\Parameter(
    parameter: 'PerPage',
    name: 'per_page',
    in: 'query',
    description: 'The number of records per page.',
    schema: new OA\Schema(type: 'integer', default: Sorting::DEFAULT_PER_PAGE, maximum: Sorting::MAX_PER_PAGE, minimum: 1),
)]
#[OA\Parameter(
    parameter: 'Page',
    name: 'page',
    in: 'query',
    description: 'The page to return.',
    schema: new OA\Schema(type: 'integer', default: 1, minimum: 1),
)]
final class ListParameters
{
    //
}
