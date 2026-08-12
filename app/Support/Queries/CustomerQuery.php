<?php

namespace App\Support\Queries;

use Illuminate\Http\Request;

/**
 * The filtering, sorting and pagination options accepted by the customer listing.
 */
final readonly class CustomerQuery
{
    /**
     * The columns the listing may be ordered by.
     *
     * @var list<string>
     */
    public const SORTABLE_COLUMNS = ['id', 'name', 'email', 'company', 'status', 'created_at'];

    /**
     * Create a new customer query.
     *
     * @param  'asc'|'desc'  $direction
     */
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * Build the query from the incoming request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->filled('search') ? (string) $request->string('search') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null,
            sort: Sorting::column($request, self::SORTABLE_COLUMNS),
            direction: Sorting::direction($request),
            perPage: Sorting::perPage($request),
            page: Sorting::page($request),
        );
    }
}
