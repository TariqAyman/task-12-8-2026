<?php

namespace App\Support\Queries;

use Illuminate\Http\Request;

/**
 * Normalizes the sorting and pagination parameters shared by every listing.
 */
final class Sorting
{
    /**
     * The largest page size a client may request.
     */
    public const MAX_PER_PAGE = 100;

    /**
     * The page size used when the client does not ask for one.
     */
    public const DEFAULT_PER_PAGE = 15;

    /**
     * Resolve the column the listing should be ordered by.
     *
     * @param  list<string>  $allowed
     */
    public static function column(Request $request, array $allowed, string $default = 'created_at'): string
    {
        $sort = (string) $request->string('sort', $default);

        return in_array($sort, $allowed, true) ? $sort : $default;
    }

    /**
     * Resolve the direction the listing should be ordered in.
     *
     * @return 'asc'|'desc'
     */
    public static function direction(Request $request): string
    {
        return $request->string('direction')->lower()->value() === 'asc' ? 'asc' : 'desc';
    }

    /**
     * Resolve the number of records to return per page.
     */
    public static function perPage(Request $request): int
    {
        return max(1, min($request->integer('per_page', self::DEFAULT_PER_PAGE), self::MAX_PER_PAGE));
    }

    /**
     * Resolve the page the client asked for.
     */
    public static function page(Request $request): int
    {
        return max(1, $request->integer('page', 1));
    }
}
