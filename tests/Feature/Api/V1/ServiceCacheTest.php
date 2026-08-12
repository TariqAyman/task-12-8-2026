<?php

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Support\ServiceCache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->headers = basicAuth(User::factory()->create());
});

/**
 * Count the select queries executed while running the given callback.
 */
function countSelects(Closure $callback): int
{
    $selects = 0;

    DB::listen(function ($query) use (&$selects): void {
        if (str_starts_with(strtolower(trim($query->sql)), 'select')) {
            $selects++;
        }
    });

    $callback();

    DB::flushQueryLog();

    return $selects;
}

it('serves a repeated service listing from the cache', function () {
    Service::factory()->count(3)->create();

    $first = countSelects(fn () => $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertOk());
    $second = countSelects(fn () => $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertOk());

    expect($second)->toBeLessThan($first);
});

it('returns the same payload from the cache as from the database', function () {
    Service::factory()->count(2)->create();

    $first = $this->withHeaders($this->headers)->getJson('/api/v1/services')->json();
    $second = $this->withHeaders($this->headers)->getJson('/api/v1/services')->json();

    expect($second)->toEqual($first);
});

it('caches each query string variation separately', function () {
    Service::factory()->count(4)->create();

    $this->withHeaders($this->headers)->getJson('/api/v1/services?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->withHeaders($this->headers)->getJson('/api/v1/services')
        ->assertOk()
        ->assertJsonCount(4, 'data');
});

it('invalidates the cache when a service is created', function () {
    $customer = Customer::factory()->create();
    Service::factory()->forCustomer($customer)->create();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(1, 'data');

    $this->withHeaders($this->headers)
        ->postJson("/api/v1/customers/{$customer->id}/services", ['name' => 'Fresh', 'price' => 20])
        ->assertCreated();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(2, 'data');
});

it('invalidates the cache when a service is updated', function () {
    $service = Service::factory()->create(['name' => 'Before']);

    $this->withHeaders($this->headers)->getJson('/api/v1/services')
        ->assertJsonPath('data.0.name', 'Before');

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/services/{$service->id}", ['name' => 'After'])
        ->assertOk();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')
        ->assertJsonPath('data.0.name', 'After');
});

it('invalidates the cache when a service is deleted', function () {
    $service = Service::factory()->create();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(1, 'data');

    $this->withHeaders($this->headers)->deleteJson("/api/v1/services/{$service->id}")->assertOk();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(0, 'data');
});

it('invalidates the cache when a customer and its services are deleted', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(2, 'data');

    $this->withHeaders($this->headers)->deleteJson("/api/v1/customers/{$customer->id}")->assertOk();

    $this->withHeaders($this->headers)->getJson('/api/v1/services')->assertJsonCount(0, 'data');
});

it('invalidates the nested customer listing as well', function () {
    $customer = Customer::factory()->create();
    Service::factory()->forCustomer($customer)->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/customers/{$customer->id}/services")
        ->assertJsonCount(1, 'data');

    $this->withHeaders($this->headers)
        ->postJson("/api/v1/customers/{$customer->id}/services", ['name' => 'Extra', 'price' => 5])
        ->assertCreated();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/customers/{$customer->id}/services")
        ->assertJsonCount(2, 'data');
});

it('flushes cached listings through the service cache on non taggable stores', function () {
    config()->set('cache.default', 'file');

    $cache = new ServiceCache;

    expect($cache->supportsTags())->toBeFalse();

    $key = $cache->key('index', ['page' => 1]);
    $cache->flush();

    expect($cache->key('index', ['page' => 1]))->not->toBe($key);
});
