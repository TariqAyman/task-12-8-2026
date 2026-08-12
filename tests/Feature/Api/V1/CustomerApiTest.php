<?php

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->headers = basicAuth(User::factory()->create());
});

it('lists customers with their service counts', function () {
    $customer = Customer::factory()->has(Service::factory()->count(3))->create();

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/customers')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $customer->id)
        ->assertJsonPath('data.0.services_count', 3)
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('paginates the customer listing', function () {
    Customer::factory()->count(25)->create();

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/customers?per_page=10&page=2')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 25);
});

it('filters the customer listing by search term and status', function () {
    Customer::factory()->create(['name' => 'Acme Industries']);
    Customer::factory()->create(['name' => 'Globex']);
    Customer::factory()->inactive()->create(['name' => 'Initech']);

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/customers?search=Acme')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Acme Industries');

    $this->withHeaders($this->headers)
        ->getJson('/api/v1/customers?status=inactive')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Initech');
});

it('creates a customer', function () {
    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+1 555 0100',
        'company' => 'Doe Consulting',
        'city' => 'Berlin',
        'country' => 'Germany',
        'status' => CustomerStatus::Active->value,
    ];

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/customers', $payload)
        ->assertCreated()
        ->assertJsonPath('data.email', 'jane@example.com')
        ->assertJsonPath('data.status', 'active');

    $this->assertDatabaseHas('customers', [
        'email' => 'jane@example.com',
        'company' => 'Doe Consulting',
    ]);
});

it('validates the customer payload on create', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/customers', ['email' => 'not-an-email', 'status' => 'archived'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'status']);
});

it('rejects a duplicate customer email', function () {
    $existing = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/customers', ['name' => 'Copy Cat', 'email' => $existing->email])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('shows a single customer', function () {
    $customer = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/v1/customers/{$customer->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $customer->id)
        ->assertJsonPath('data.email', $customer->email);
});

it('returns 404 for a customer that does not exist', function () {
    $this->withHeaders($this->headers)
        ->getJson('/api/v1/customers/9999')
        ->assertNotFound();
});

it('updates a customer', function () {
    $customer = Customer::factory()->create(['name' => 'Old Name']);

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'New Name',
            'status' => CustomerStatus::Inactive->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.status', 'inactive');

    expect($customer->refresh()->name)->toBe('New Name')
        ->and($customer->status)->toBe(CustomerStatus::Inactive);
});

it('allows a customer to keep its own email on update', function () {
    $customer = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'Same Email',
            'email' => $customer->email,
        ])
        ->assertOk();
});

it('rejects an email already used by another customer on update', function () {
    $customer = Customer::factory()->create();
    $other = Customer::factory()->create();

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/customers/{$customer->id}", ['email' => $other->email])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('deletes a customer together with its services', function () {
    $customer = Customer::factory()->has(Service::factory()->count(2))->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/customers/{$customer->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Customer deleted successfully.');

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);

    expect($customer->services()->count())->toBe(0);
});
