<?php

namespace App\Services;

use App\Http\Resources\ServiceResource;
use App\Models\Customer;
use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Support\Queries\ServiceQuery;
use App\Support\ServiceCache;

/**
 * The service use cases sitting between the controllers and the repositories.
 */
class ServiceService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ServiceRepositoryInterface $services,
        protected ServiceCache $cache,
    ) {}

    /**
     * Get the cached listing payload for the given query.
     *
     * The rendered payload is cached rather than the models themselves, because
     * the framework ships with `cache.serializable_classes` disabled.
     *
     * @return array<string, mixed>
     */
    public function cachedListing(ServiceQuery $query): array
    {
        return $this->cache->remember(
            $query->cacheGroup(),
            $query->cacheContext(),
            fn (): array => $this->listing($query),
        );
    }

    /**
     * Build the uncached listing payload for the given query.
     *
     * @return array<string, mixed>
     */
    public function listing(ServiceQuery $query): array
    {
        /** @var array<string, mixed> $payload */
        $payload = ServiceResource::collection($this->services->paginate($query))
            ->response()
            ->getData(true);

        return $payload;
    }

    /**
     * Get a single service ready to be returned to the client.
     */
    public function show(Service $service): Service
    {
        return $this->services->withCustomer($service);
    }

    /**
     * Create a service for the given customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForCustomer(Customer $customer, array $attributes): Service
    {
        $service = $this->services->createForCustomer($customer, $attributes);

        $this->cache->flush();

        return $service;
    }

    /**
     * Update a service.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Service $service, array $attributes): Service
    {
        $service = $this->services->update($service, $attributes);

        $this->cache->flush();

        return $this->services->withCustomer($service);
    }

    /**
     * Delete a service.
     */
    public function delete(Service $service): void
    {
        $this->services->delete($service);

        $this->cache->flush();
    }
}
