<?php

namespace App\Providers;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every repository contract to its Eloquent implementation, so the
 * service layer depends on interfaces rather than on Eloquent itself.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CustomerRepositoryInterface::class => CustomerRepository::class,
        ServiceRepositoryInterface::class => ServiceRepository::class,
    ];
}
