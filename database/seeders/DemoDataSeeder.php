<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\CustomerStatus;
use App\Enums\ServiceStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a deterministic data set for exploring the API.
 *
 * Unlike the factory based seeders this one uses no Faker, so it also runs
 * inside the production container where dev dependencies are absent.
 */
class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed an API user together with a few customers and their services.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        foreach ($this->customers() as $attributes) {
            $services = $attributes['services'];
            unset($attributes['services']);

            $customer = Customer::firstOrCreate(['email' => $attributes['email']], $attributes);

            foreach ($services as $service) {
                $customer->services()->firstOrCreate(['name' => $service['name']], $service);
            }
        }
    }

    /**
     * Get the demo customers and the services that belong to them.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function customers(): array
    {
        return [
            [
                'name' => 'Jane Doe',
                'email' => 'jane@acme.test',
                'phone' => '+1 555 0100',
                'company' => 'Acme Industries',
                'address' => '1 Market Street',
                'city' => 'Berlin',
                'country' => 'Germany',
                'status' => CustomerStatus::Active,
                'services' => [
                    [
                        'name' => 'Managed Hosting',
                        'description' => 'Fully managed hosting with daily backups.',
                        'price' => 149.99,
                        'currency' => 'EUR',
                        'billing_cycle' => BillingCycle::Monthly,
                        'status' => ServiceStatus::Active,
                        'starts_at' => '2026-01-01',
                    ],
                    [
                        'name' => 'Domain Registration',
                        'description' => 'Annual renewal of acme.test.',
                        'price' => 18.00,
                        'currency' => 'EUR',
                        'billing_cycle' => BillingCycle::Yearly,
                        'status' => ServiceStatus::Active,
                        'starts_at' => '2026-02-15',
                    ],
                ],
            ],
            [
                'name' => 'John Smith',
                'email' => 'john@globex.test',
                'phone' => '+44 20 7946 0100',
                'company' => 'Globex Corporation',
                'city' => 'London',
                'country' => 'United Kingdom',
                'status' => CustomerStatus::Active,
                'services' => [
                    [
                        'name' => 'SEO Retainer',
                        'description' => 'Quarterly search optimisation retainer.',
                        'price' => 2400.00,
                        'currency' => 'GBP',
                        'billing_cycle' => BillingCycle::Quarterly,
                        'status' => ServiceStatus::Active,
                        'starts_at' => '2026-03-01',
                    ],
                    [
                        'name' => 'Onboarding Workshop',
                        'description' => 'One off onboarding workshop.',
                        'price' => 900.00,
                        'currency' => 'GBP',
                        'billing_cycle' => BillingCycle::OneTime,
                        'status' => ServiceStatus::Cancelled,
                        'starts_at' => '2026-03-05',
                        'ends_at' => '2026-03-05',
                    ],
                ],
            ],
            [
                'name' => 'Amara Okafor',
                'email' => 'amara@initech.test',
                'company' => 'Initech',
                'city' => 'Lagos',
                'country' => 'Nigeria',
                'status' => CustomerStatus::Inactive,
                'services' => [
                    [
                        'name' => 'Cloud Backup',
                        'description' => 'Off site nightly backups, currently suspended.',
                        'price' => 39.50,
                        'currency' => 'USD',
                        'billing_cycle' => BillingCycle::Monthly,
                        'status' => ServiceStatus::Suspended,
                        'starts_at' => '2025-11-01',
                    ],
                ],
            ],
        ];
    }
}
