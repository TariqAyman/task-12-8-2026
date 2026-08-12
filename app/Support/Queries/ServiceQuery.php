<?php

namespace App\Support\Queries;

use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * The filtering, sorting and pagination options accepted by the service listings.
 */
final readonly class ServiceQuery
{
    /**
     * The columns the listing may be ordered by.
     *
     * @var list<string>
     */
    public const SORTABLE_COLUMNS = ['id', 'name', 'price', 'status', 'billing_cycle', 'starts_at', 'created_at'];

    /**
     * Create a new service query.
     *
     * @param  'asc'|'desc'  $direction
     */
    public function __construct(
        public ?int $customerId = null,
        public ?string $search = null,
        public ?string $status = null,
        public ?string $billingCycle = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * Build the query from the incoming request.
     *
     * When a customer is given the listing is locked to it and the `customer_id`
     * query parameter is ignored.
     */
    public static function fromRequest(Request $request, ?Customer $customer = null): self
    {
        return new self(
            customerId: $customer->id ?? ($request->filled('customer_id') ? $request->integer('customer_id') : null),
            search: $request->filled('search') ? (string) $request->string('search') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null,
            billingCycle: $request->filled('billing_cycle') ? (string) $request->string('billing_cycle') : null,
            sort: Sorting::column($request, self::SORTABLE_COLUMNS),
            direction: Sorting::direction($request),
            perPage: Sorting::perPage($request),
            page: Sorting::page($request),
        );
    }

    /**
     * Get the cache context that uniquely identifies the resulting listing.
     *
     * @return array<string, mixed>
     */
    public function cacheContext(): array
    {
        return [
            'customer_id' => $this->customerId,
            'search' => $this->search,
            'status' => $this->status,
            'billing_cycle' => $this->billingCycle,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }

    /**
     * Get the cache group the listing belongs to.
     */
    public function cacheGroup(): string
    {
        return $this->customerId === null ? 'index' : "customer.{$this->customerId}";
    }
}
