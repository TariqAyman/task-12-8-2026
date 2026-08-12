<?php

namespace App\Support;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Caches the service listing payloads and centralises their invalidation.
 *
 * Only plain arrays are stored, never Eloquent objects, because the framework
 * ships with `cache.serializable_classes` disabled. Taggable stores (Redis,
 * array) are flushed by tag; stores that cannot tag (database, file) fall back
 * to a version counter that is embedded in every key.
 */
class ServiceCache
{
    /**
     * The cache tag and key namespace used for every service listing.
     */
    public const NAMESPACE = 'services';

    /**
     * The key holding the namespace version for non-taggable stores.
     */
    public const VERSION_KEY = 'services:version';

    /**
     * Create a new service cache instance.
     */
    public function __construct(protected ?int $ttlSeconds = null)
    {
        $this->ttlSeconds ??= (int) config('cache.service_ttl', 300);
    }

    /**
     * Resolve the cached listing payload, executing the callback on a miss.
     *
     * @param  array<string, mixed>  $context
     * @param  Closure(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public function remember(string $group, array $context, Closure $callback): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->repository()->remember(
            $this->key($group, $context),
            $this->ttlSeconds,
            $callback,
        );

        return $payload;
    }

    /**
     * Invalidate every cached service listing.
     */
    public function flush(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::NAMESPACE])->flush();

            return;
        }

        $version = (int) Cache::get(self::VERSION_KEY, 1);

        Cache::forever(self::VERSION_KEY, $version + 1);
    }

    /**
     * Build the cache key for a listing, namespaced by group and query context.
     *
     * @param  array<string, mixed>  $context
     */
    public function key(string $group, array $context): string
    {
        ksort($context);

        $fingerprint = md5((string) json_encode($context));

        if ($this->supportsTags()) {
            return self::NAMESPACE.":{$group}:{$fingerprint}";
        }

        return self::NAMESPACE.':v'.$this->version().":{$group}:{$fingerprint}";
    }

    /**
     * Get the current namespace version used by non-taggable stores.
     */
    public function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Determine whether the configured cache store supports tagging.
     */
    public function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Get the repository that listings should be read from and written to.
     */
    protected function repository(): Repository
    {
        if ($this->supportsTags()) {
            /** @var Repository $repository */
            $repository = Cache::tags([self::NAMESPACE]);

            return $repository;
        }

        return Cache::store();
    }
}
