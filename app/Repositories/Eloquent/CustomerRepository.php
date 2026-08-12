<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Support\Queries\CustomerQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The Eloquent backed customer repository.
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Get a paginated listing of the customers matching the given query.
     *
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(CustomerQuery $query): LengthAwarePaginator
    {
        return $this->query($query)
            ->paginate(perPage: $query->perPage, page: $query->page)
            ->withQueryString();
    }

    /**
     * Get the customer with its service count loaded.
     */
    public function withServiceCount(Customer $customer): Customer
    {
        return $customer->loadCount('services');
    }

    /**
     * Persist a new customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer
    {
        return Customer::create($attributes);
    }

    /**
     * Update the given customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer;
    }

    /**
     * Delete the given customer.
     */
    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    /**
     * Build the filtered and sorted customer query.
     *
     * @return Builder<Customer>
     */
    protected function query(CustomerQuery $query): Builder
    {
        return Customer::query()
            ->withCount('services')
            ->when($query->search !== null, fn (Builder $builder) => $builder->search((string) $query->search))
            ->when($query->status !== null, fn (Builder $builder) => $builder->where('status', $query->status))
            ->orderBy($query->sort, $query->direction);
    }
}
