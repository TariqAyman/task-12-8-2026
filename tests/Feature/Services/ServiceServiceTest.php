<?php

use App\Models\Customer;
use App\Models\Service;
use App\Services\ServiceService;
use App\Support\Queries\ServiceQuery;

beforeEach(function () {
    $this->services = app(ServiceService::class);
});

it('renders the listing payload with its pagination envelope', function () {
    Service::factory()->count(2)->create();

    $payload = $this->services->listing(new ServiceQuery);

    expect($payload)->toHaveKeys(['data', 'links', 'meta'])
        ->and($payload['data'])->toHaveCount(2)
        ->and($payload['meta']['total'])->toBe(2);
});

it('serves a repeated listing from the cache', function () {
    Service::factory()->create();

    $query = new ServiceQuery;

    expect($this->services->cachedListing($query))->toBe($this->services->cachedListing($query));

    Service::factory()->create();

    expect($this->services->cachedListing($query)['meta']['total'])->toBe(1);
});

it('invalidates the cache when a service is created', function () {
    Service::factory()->create();

    expect($this->services->cachedListing(new ServiceQuery)['meta']['total'])->toBe(1);

    $this->services->createForCustomer(Customer::factory()->create(), ['name' => 'Extra', 'price' => 10]);

    expect($this->services->cachedListing(new ServiceQuery)['meta']['total'])->toBe(2);
});

it('invalidates the cache when a service is updated', function () {
    $service = Service::factory()->create(['name' => 'Before']);

    expect($this->services->cachedListing(new ServiceQuery)['data'][0]['name'])->toBe('Before');

    $this->services->update($service, ['name' => 'After']);

    expect($this->services->cachedListing(new ServiceQuery)['data'][0]['name'])->toBe('After');
});

it('invalidates the cache when a service is deleted', function () {
    $service = Service::factory()->create();

    expect($this->services->cachedListing(new ServiceQuery)['meta']['total'])->toBe(1);

    $this->services->delete($service);

    expect($this->services->cachedListing(new ServiceQuery)['meta']['total'])->toBe(0);
});

it('returns the updated service with its customer loaded', function () {
    $service = Service::factory()->create(['name' => 'Before']);

    $updated = $this->services->update($service, ['name' => 'After']);

    expect($updated->name)->toBe('After')
        ->and($updated->relationLoaded('customer'))->toBeTrue();
});

it('caches each customer listing separately', function () {
    $customer = Customer::factory()->create();
    Service::factory()->forCustomer($customer)->create();
    Service::factory()->count(2)->create();

    $forCustomer = $this->services->cachedListing(new ServiceQuery(customerId: $customer->id));
    $forEveryone = $this->services->cachedListing(new ServiceQuery);

    expect($forCustomer['meta']['total'])->toBe(1)
        ->and($forEveryone['meta']['total'])->toBe(3);
});
