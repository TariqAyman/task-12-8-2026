<?php

use App\Enums\BillingCycle;
use App\Enums\ServiceStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->headers = basicAuth(User::factory()->create());
});

it('creates a service for a customer', function () {
    $customer = Customer::factory()->create();

    $payload = [
        'name' => 'Managed Hosting',
        'description' => 'Fully managed hosting with daily backups.',
        'price' => 149.99,
        'currency' => 'eur',
        'billing_cycle' => BillingCycle::Monthly->value,
        'status' => ServiceStatus::Active->value,
        'starts_at' => '2026-01-01',
        'ends_at' => '2026-12-31',
    ];

    $this->withHeaders($this->headers)
        ->postJson("/api/v1/customers/{$customer->id}/services", $payload)
        ->assertCreated()
        ->assertJsonPath('data.name', 'Managed Hosting')
        ->assertJsonPath('data.customer_id', $customer->id)
        ->assertJsonPath('data.currency', 'EUR')
        ->assertJsonPath('data.price', 149.99)
        ->assertJsonPath('data.annual_value', 1799.88);

    $this->assertDatabaseHas('services', [
        'customer_id' => $customer->id,
        'name' => 'Managed Hosting',
    ]);
});

it('validates the service payload on create', function () {
    $customer = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/v1/customers/{$customer->id}/services", [
            'price' => 'free',
            'billing_cycle' => 'fortnightly',
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-01-01',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'billing_cycle', 'ends_at']);
});

it('does not create a service for a customer that does not exist', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/customers/9999/services', ['name' => 'Ghost', 'price' => 10])
        ->assertNotFound();
});

it('lists the services of a single customer', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();
    Customer::factory()->has(Service::factory()->count(3))->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/customers/{$customer->id}/services")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.customer_id', $customer->id)
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('lists every service across customers', function () {
    Customer::factory()->has(Service::factory()->count(2))->create();
    Customer::factory()->has(Service::factory()->count(3))->create();

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/services')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('filters the service listing by customer and status', function () {
    $customer = Customer::factory()->create();
    Service::factory()->forCustomer($customer)->count(2)->create();
    Service::factory()->forCustomer($customer)->cancelled()->create();
    Service::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/services?customer_id={$customer->id}")
        ->assertOk()
        ->assertJsonCount(3, 'data');

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/services?status='.ServiceStatus::Cancelled->value)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('shows a single service with its customer', function () {
    $service = Service::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/services/{$service->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $service->id)
        ->assertJsonPath('data.customer.id', $service->customer_id);
});

it('updates a service', function () {
    $service = Service::factory()->create(['name' => 'Old Plan']);

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/services/{$service->id}", [
            'name' => 'New Plan',
            'price' => 250,
            'status' => ServiceStatus::Suspended->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Plan')
        ->assertJsonPath('data.price', fn (int|float $price): bool => (float) $price === 250.0)
        ->assertJsonPath('data.status', 'suspended');

    expect($service->refresh()->status)->toBe(ServiceStatus::Suspended);
});

it('can move a service to another customer', function () {
    $service = Service::factory()->create();
    $target = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/services/{$service->id}", ['customer_id' => $target->id])
        ->assertOk()
        ->assertJsonPath('data.customer_id', $target->id);
});

it('rejects moving a service to a customer that does not exist', function () {
    $service = Service::factory()->create();

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/services/{$service->id}", ['customer_id' => 9999])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

it('deletes a service', function () {
    $service = Service::factory()->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/services/{$service->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Service deleted successfully.');

    $this->assertSoftDeleted('services', ['id' => $service->id]);
});

it('never exposes another customer services through the nested listing', function () {
    $customerA = Customer::factory()->create();
    $customerB = Customer::factory()->create();
    $serviceOfB = Service::factory()->forCustomer($customerB)->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/customers/{$customerA->id}/services")
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $serviceOfB->id]);
});
