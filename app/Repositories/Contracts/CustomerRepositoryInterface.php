<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use App\Support\Queries\CustomerQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The persistence contract for customers.
 */
interface CustomerRepositoryInterface
{
    /**
     * Get a paginated listing of the customers matching the given query.
     *
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(CustomerQuery $query): LengthAwarePaginator;

    /**
     * Get the customer with its service count loaded.
     */
    public function withServiceCount(Customer $customer): Customer;

    /**
     * Persist a new customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer;

    /**
     * Update the given customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer;

    /**
     * Delete the given customer.
     */
    public function delete(Customer $customer): void;
}
