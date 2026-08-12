<?php

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Eloquent\ServiceRepository;
use App\Support\Queries\ServiceQuery;

beforeEach(function () {
    $this->repository = app(ServiceRepositoryInterface::class);
});

it('resolves the eloquent implementation from the container', function () {
    expect($this->repository)->toBeInstanceOf(ServiceRepository::class);
});

it('restricts the listing to a single customer', function () {
    $customer = Customer::factory()->create();
    Service::factory()->count(2)->forCustomer($customer)->create();
    Service::factory()->count(3)->create();

    expect($this->repository->paginate(new ServiceQuery(customerId: $customer->id))->total())->toBe(2)
        ->and($this->repository->paginate(new ServiceQuery)->total())->toBe(5);
});

it('filters the listing by search term, status and billing cycle', function () {
    Service::factory()->create(['name' => 'Managed Hosting', 'status' => 'active', 'billing_cycle' => 'yearly']);
    Service::factory()->create(['name' => 'Backups', 'status' => 'pending', 'billing_cycle' => 'monthly']);

    expect($this->repository->paginate(new ServiceQuery(search: 'Hosting'))->total())->toBe(1)
        ->and($this->repository->paginate(new ServiceQuery(status: 'pending'))->total())->toBe(1)
        ->and($this->repository->paginate(new ServiceQuery(billingCycle: 'yearly'))->total())->toBe(1);
});

it('sorts the listing by the requested column and direction', function () {
    Service::factory()->create(['price' => 10]);
    Service::factory()->create(['price' => 90]);

    $cheapestFirst = $this->repository->paginate(new ServiceQuery(sort: 'price', direction: 'asc'));

    expect((float) $cheapestFirst->items()[0]->price)->toBe(10.0);
});

it('creates a service for a customer', function () {
    $customer = Customer::factory()->create();

    $service = $this->repository->createForCustomer($customer, ['name' => 'Managed Hosting', 'price' => 149.99]);

    expect($service->customer_id)->toBe($customer->id);
});

it('updates and deletes a service', function () {
    $service = Service::factory()->create(['name' => 'Before']);

    $this->repository->update($service, ['name' => 'After']);

    expect($service->fresh()->name)->toBe('After');

    $this->repository->delete($service);

    expect(Service::query()->find($service->id))->toBeNull();
});

it('deletes every service belonging to a customer', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();
    Service::factory()->create();

    $this->repository->deleteForCustomer($customer);

    expect(Service::query()->count())->toBe(1);
});

it('loads the customer of a service', function () {
    $service = Service::factory()->create();

    expect($this->repository->withCustomer($service)->relationLoaded('customer'))->toBeTrue();
});
