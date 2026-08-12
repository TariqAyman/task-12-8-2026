<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Support\Queries\CustomerQuery;
use App\Support\ServiceCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * The customer use cases sitting between the controllers and the repositories.
 */
class CustomerService
{
    /**
     * Create a new customer service instance.
     */
    public function __construct(
        protected CustomerRepositoryInterface $customers,
        protected ServiceRepositoryInterface $services,
        protected ServiceCache $cache,
    ) {}

    /**
     * Get a paginated listing of customers.
     *
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(CustomerQuery $query): LengthAwarePaginator
    {
        return $this->customers->paginate($query);
    }

    /**
     * Get a single customer ready to be returned to the client.
     */
    public function show(Customer $customer): Customer
    {
        return $this->customers->withServiceCount($customer);
    }

    /**
     * Create a customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer
    {
        return $this->customers->create($attributes);
    }

    /**
     * Update a customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer
    {
        return $this->customers->withServiceCount(
            $this->customers->update($customer, $attributes),
        );
    }

    /**
     * Delete a customer together with every service that belongs to it.
     *
     * The cached service listings are invalidated because the deleted services
     * are no longer part of them.
     */
    public function delete(Customer $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $this->services->deleteForCustomer($customer);
            $this->customers->delete($customer);
        });

        $this->cache->flush();
    }
}
