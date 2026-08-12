<?php

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\CustomerService;
use App\Support\Queries\CustomerQuery;
use App\Support\ServiceCache;

beforeEach(function () {
    $this->customers = app(CustomerService::class);
});

/**
 * Build a customer service whose collaborators can be swapped out.
 */
function customerServiceWith(?CustomerRepositoryInterface $customers = null, ?ServiceCache $cache = null): CustomerService
{
    return new CustomerService(
        $customers ?? app(CustomerRepositoryInterface::class),
        app(ServiceRepositoryInterface::class),
        $cache ?? app(ServiceCache::class),
    );
}

it('paginates customers through the repository', function () {
    Customer::factory()->count(3)->create();

    expect($this->customers->paginate(new CustomerQuery)->total())->toBe(3);
});

it('creates a customer', function () {
    $customer = $this->customers->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $this->assertDatabaseHas('customers', ['id' => $customer->id, 'email' => 'jane@example.com']);
});

it('returns the service count with the updated customer', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();

    $updated = $this->customers->update($customer, ['name' => 'Renamed']);

    expect($updated->name)->toBe('Renamed')
        ->and($updated->services_count)->toBe(2);
});

it('deletes the customer together with its services', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();

    $this->customers->delete($customer);

    expect(Customer::query()->count())->toBe(0)
        ->and(Service::query()->count())->toBe(0)
        ->and(Service::withTrashed()->count())->toBe(2);
});

it('rolls the deleted services back when the customer cannot be deleted', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();

    $customers = Mockery::mock(CustomerRepositoryInterface::class);
    $customers->shouldReceive('delete')->once()->andThrow(new RuntimeException('the customer could not be deleted'));

    $cache = Mockery::mock(ServiceCache::class);
    $cache->shouldNotReceive('flush');

    expect(fn () => customerServiceWith($customers, $cache)->delete($customer))
        ->toThrow(RuntimeException::class);

    expect(Customer::query()->count())->toBe(1)
        ->and(Service::query()->count())->toBe(2);
});

it('invalidates the cached service listings when a customer is deleted', function () {
    $cache = Mockery::mock(ServiceCache::class);
    $cache->shouldReceive('flush')->once();

    customerServiceWith(cache: $cache)->delete(Customer::factory()->create());
});
