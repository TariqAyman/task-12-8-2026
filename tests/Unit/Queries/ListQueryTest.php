<?php

use App\Models\Customer;
use App\Support\Queries\CustomerQuery;
use App\Support\Queries\ServiceQuery;
use App\Support\Queries\Sorting;
use Illuminate\Http\Request;

/**
 * Build a GET request carrying the given query string parameters.
 *
 * @param  array<string, mixed>  $parameters
 */
function listRequest(array $parameters = []): Request
{
    return Request::create('/api/v1/services', 'GET', $parameters);
}

it('reads the customer listing options from the request', function () {
    $query = CustomerQuery::fromRequest(listRequest([
        'search' => 'Acme',
        'status' => 'inactive',
        'sort' => 'name',
        'direction' => 'ASC',
        'per_page' => '25',
        'page' => '3',
    ]));

    expect($query->search)->toBe('Acme')
        ->and($query->status)->toBe('inactive')
        ->and($query->sort)->toBe('name')
        ->and($query->direction)->toBe('asc')
        ->and($query->perPage)->toBe(25)
        ->and($query->page)->toBe(3);
});

it('falls back to the listing defaults', function () {
    $query = CustomerQuery::fromRequest(listRequest());

    expect($query->search)->toBeNull()
        ->and($query->status)->toBeNull()
        ->and($query->sort)->toBe('created_at')
        ->and($query->direction)->toBe('desc')
        ->and($query->perPage)->toBe(Sorting::DEFAULT_PER_PAGE)
        ->and($query->page)->toBe(1);
});

it('rejects a sort column that is not allow listed', function () {
    expect(CustomerQuery::fromRequest(listRequest(['sort' => 'password']))->sort)->toBe('created_at')
        ->and(ServiceQuery::fromRequest(listRequest(['sort' => 'customer_id']))->sort)->toBe('created_at');
});

it('clamps the page size to the supported range', function (string $perPage, int $expected) {
    expect(CustomerQuery::fromRequest(listRequest(['per_page' => $perPage]))->perPage)->toBe($expected);
})->with([
    ['0', 1],
    ['-5', 1],
    ['1000', Sorting::MAX_PER_PAGE],
    ['20', 20],
]);

it('locks the service listing to the given customer', function () {
    $customer = new Customer;
    $customer->id = 7;

    $query = ServiceQuery::fromRequest(listRequest(['customer_id' => '99']), $customer);

    expect($query->customerId)->toBe(7)
        ->and($query->cacheGroup())->toBe('customer.7');
});

it('reads the customer filter from the query string when no customer is given', function () {
    $query = ServiceQuery::fromRequest(listRequest(['customer_id' => '99']));

    expect($query->customerId)->toBe(99)
        ->and($query->cacheGroup())->toBe('customer.99');
});

it('groups the unfiltered service listing under the index cache group', function () {
    expect(ServiceQuery::fromRequest(listRequest())->cacheGroup())->toBe('index');
});

it('exposes every option that changes the listing in the cache context', function () {
    $context = ServiceQuery::fromRequest(listRequest([
        'search' => 'hosting',
        'status' => 'active',
        'billing_cycle' => 'yearly',
        'sort' => 'price',
        'direction' => 'asc',
        'per_page' => '5',
        'page' => '2',
    ]))->cacheContext();

    expect($context)->toBe([
        'customer_id' => null,
        'search' => 'hosting',
        'status' => 'active',
        'billing_cycle' => 'yearly',
        'sort' => 'price',
        'direction' => 'asc',
        'per_page' => 5,
        'page' => 2,
    ]);
});
