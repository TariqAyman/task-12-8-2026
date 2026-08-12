<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\ServiceStatus;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->randomElement(['Web Hosting', 'Domain Registration', 'SEO Retainer', 'Cloud Backup', 'Support Plan']).' '.fake()->bothify('##'),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 5000),
            'currency' => 'USD',
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'status' => ServiceStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => fake()->optional()->dateTimeBetween($startsAt, '+1 year'),
        ];
    }

    /**
     * Indicate that the service is awaiting activation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceStatus::Pending,
        ]);
    }

    /**
     * Indicate that the service has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceStatus::Cancelled,
            'ends_at' => now(),
        ]);
    }

    /**
     * Indicate that the service belongs to the given customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes): array => [
            'customer_id' => $customer->id,
        ]);
    }
}
