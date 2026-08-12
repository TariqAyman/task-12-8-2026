<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a handful of customers, each with a few services.
     */
    public function run(): void
    {
        Customer::factory()
            ->count(10)
            ->has(Service::factory()->count(3))
            ->create();
    }
}
