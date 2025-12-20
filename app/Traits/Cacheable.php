<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Cacheable Trait
 * 
 * Provides query caching functionality
 */
trait Cacheable
{
    /**
     * Cache query results
     * 
     * @param string $key
     * @param int $ttl Time to live in seconds
     * @param \Closure $callback
     * @return mixed
     */
    public function cacheQuery(string $key, int $ttl, \Closure $callback)
    {
        $cacheKey = $this->getCacheKey($key);
        
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear cache for this model
     * 
     * @param string|null $key
     * @return bool
     */
    public function clearCache(?string $key = null): bool
    {
        if ($key) {
            return Cache::forget($this->getCacheKey($key));
        }

        // Clear all cache for this model
        return Cache::flush();
    }

    /**
     * Get cache key with prefix
     * 
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        $modelName = strtolower(class_basename($this));
        return "{$modelName}.{$key}";
    }

    /**
     * Get cache TTL
     * Override this in your model to define cache TTL
     * 
     * @return int
     */
    public function getCacheTTL(): int
    {
        return $this->cacheTTL ?? 3600; // 1 hour default
    }

    /**
     * Boot the trait
     */
    protected static function bootCacheable(): void
    {
        // Clear cache on model events
        static::saved(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
}
