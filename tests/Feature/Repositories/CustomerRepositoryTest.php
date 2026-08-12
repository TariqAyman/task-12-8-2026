<?php

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Eloquent\CustomerRepository;
use App\Support\Queries\CustomerQuery;

beforeEach(function () {
    $this->repository = app(CustomerRepositoryInterface::class);
});

it('resolves the eloquent implementation from the container', function () {
    expect($this->repository)->toBeInstanceOf(CustomerRepository::class);
});

it('paginates customers with their service counts', function () {
    Customer::factory()->has(Service::factory()->count(2))->create();
    Customer::factory()->count(4)->create();

    $customers = $this->repository->paginate(new CustomerQuery(perPage: 2, page: 2));

    expect($customers->total())->toBe(5)
        ->and($customers->currentPage())->toBe(2)
        ->and($customers->items())->toHaveCount(2)
        ->and($customers->items()[0]->services_count)->not->toBeNull();
});

it('filters the listing by search term and status', function () {
    Customer::factory()->create(['name' => 'Acme Industries']);
    Customer::factory()->create(['company' => 'Acme Holding']);
    Customer::factory()->inactive()->create(['name' => 'Initech']);

    expect($this->repository->paginate(new CustomerQuery(search: 'Acme'))->total())->toBe(2)
        ->and($this->repository->paginate(new CustomerQuery(status: 'inactive'))->total())->toBe(1);
});

it('sorts the listing by the requested column and direction', function () {
    Customer::factory()->create(['name' => 'Beta']);
    Customer::factory()->create(['name' => 'Alpha']);

    $ascending = $this->repository->paginate(new CustomerQuery(sort: 'name', direction: 'asc'));
    $descending = $this->repository->paginate(new CustomerQuery(sort: 'name', direction: 'desc'));

    expect($ascending->items()[0]->name)->toBe('Alpha')
        ->and($descending->items()[0]->name)->toBe('Beta');
});

it('creates, updates and deletes a customer', function () {
    $customer = $this->repository->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    expect($customer->exists)->toBeTrue();

    $this->repository->update($customer, ['name' => 'Jane Roe']);

    expect($customer->fresh()->name)->toBe('Jane Roe');

    $this->repository->delete($customer);

    expect(Customer::query()->find($customer->id))->toBeNull()
        ->and(Customer::withTrashed()->find($customer->id))->not->toBeNull();
});

it('loads the service count for a single customer', function () {
    $customer = Customer::factory()->has(Service::factory()->count(3))->create();

    expect($this->repository->withServiceCount($customer)->services_count)->toBe(3);
});
