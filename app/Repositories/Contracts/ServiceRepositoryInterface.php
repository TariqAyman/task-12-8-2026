<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use App\Models\Service;
use App\Support\Queries\ServiceQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The persistence contract for services.
 */
interface ServiceRepositoryInterface
{
    /**
     * Get a paginated listing of the services matching the given query.
     *
     * @return LengthAwarePaginator<int, Service>
     */
    public function paginate(ServiceQuery $query): LengthAwarePaginator;

    /**
     * Get the service with its customer loaded.
     */
    public function withCustomer(Service $service): Service;

    /**
     * Persist a new service for the given customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForCustomer(Customer $customer, array $attributes): Service;

    /**
     * Update the given service.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Service $service, array $attributes): Service;

    /**
     * Delete the given service.
     */
    public function delete(Service $service): void;

    /**
     * Delete every service belonging to the given customer.
     */
    public function deleteForCustomer(Customer $customer): void;
}
