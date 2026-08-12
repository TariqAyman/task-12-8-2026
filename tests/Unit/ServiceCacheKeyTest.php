<?php

use App\Support\ServiceCache;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::store('file')->forget(ServiceCache::VERSION_KEY);
});

it('builds a stable key regardless of context ordering', function () {
    $cache = new ServiceCache;

    expect($cache->key('index', ['page' => 2, 'status' => 'active']))
        ->toBe($cache->key('index', ['status' => 'active', 'page' => 2]));
});

it('builds a different key for a different context', function () {
    $cache = new ServiceCache;

    expect($cache->key('index', ['page' => 1]))
        ->not->toBe($cache->key('index', ['page' => 2]));
});

it('builds a different key per listing group', function () {
    $cache = new ServiceCache;

    expect($cache->key('index', ['page' => 1]))
        ->not->toBe($cache->key('customer.1', ['page' => 1]));
});

it('omits the version segment on taggable stores', function () {
    config()->set('cache.default', 'array');

    $cache = new ServiceCache;

    expect($cache->supportsTags())->toBeTrue()
        ->and($cache->key('index', []))->toStartWith('services:index:');
});

it('embeds the version segment on stores that cannot tag', function () {
    config()->set('cache.default', 'file');

    $cache = new ServiceCache;

    expect($cache->supportsTags())->toBeFalse()
        ->and($cache->key('index', []))->toStartWith('services:v1:index:');
});

it('bumps the version when flushing a store that cannot tag', function () {
    config()->set('cache.default', 'file');

    $cache = new ServiceCache;

    expect($cache->version())->toBe(1);

    $cache->flush();

    expect($cache->version())->toBe(2)
        ->and($cache->key('index', []))->toStartWith('services:v2:index:');
});

it('remembers a payload and re-runs the callback after a flush', function () {
    config()->set('cache.default', 'array');

    $cache = new ServiceCache;
    $calls = 0;

    $callback = function () use (&$calls): array {
        $calls++;

        return ['data' => $calls];
    };

    expect($cache->remember('index', [], $callback))->toBe(['data' => 1])
        ->and($cache->remember('index', [], $callback))->toBe(['data' => 1])
        ->and($calls)->toBe(1);

    $cache->flush();

    expect($cache->remember('index', [], $callback))->toBe(['data' => 2])
        ->and($calls)->toBe(2);
});
