<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Support\Queries\ServiceQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The Eloquent backed service repository.
 */
class ServiceRepository implements ServiceRepositoryInterface
{
    /**
     * Get a paginated listing of the services matching the given query.
     *
     * @return LengthAwarePaginator<int, Service>
     */
    public function paginate(ServiceQuery $query): LengthAwarePaginator
    {
        return $this->query($query)
            ->paginate(perPage: $query->perPage, page: $query->page)
            ->withQueryString();
    }

    /**
     * Get the service with its customer loaded.
     */
    public function withCustomer(Service $service): Service
    {
        return $service->load('customer');
    }

    /**
     * Persist a new service for the given customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForCustomer(Customer $customer, array $attributes): Service
    {
        return $customer->services()->create($attributes);
    }

    /**
     * Update the given service.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Service $service, array $attributes): Service
    {
        $service->update($attributes);

        return $service;
    }

    /**
     * Delete the given service.
     */
    public function delete(Service $service): void
    {
        $service->delete();
    }

    /**
     * Delete every service belonging to the given customer.
     */
    public function deleteForCustomer(Customer $customer): void
    {
        $customer->services()->delete();
    }

    /**
     * Build the filtered and sorted service query.
     *
     * @return Builder<Service>
     */
    protected function query(ServiceQuery $query): Builder
    {
        return Service::query()
            ->when($query->customerId !== null, fn (Builder $builder) => $builder->where('customer_id', $query->customerId))
            ->when($query->search !== null, fn (Builder $builder) => $builder->search((string) $query->search))
            ->when($query->status !== null, fn (Builder $builder) => $builder->where('status', $query->status))
            ->when($query->billingCycle !== null, fn (Builder $builder) => $builder->where('billing_cycle', $query->billingCycle))
            ->orderBy($query->sort, $query->direction);
    }
}
