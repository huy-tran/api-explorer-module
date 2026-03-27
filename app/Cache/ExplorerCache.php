<?php

namespace Modules\ApiExplorer\Cache;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ExplorerCache
{
    private const string ENDPOINTS_KEY = 'api_explorer.endpoints';

    private const string SCANNED_AT_KEY = 'api_explorer.scanned_at';

    public function isEnabled(): bool
    {
        return (bool) config('api-explorer.cache', true);
    }

    public function get(): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return Cache::get(self::ENDPOINTS_KEY);
    }

    public function put(array $data): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $ttl = (int) config('api-explorer.cache_ttl', 86400);
        Cache::put(self::ENDPOINTS_KEY, $data, $ttl);
        Cache::put(self::SCANNED_AT_KEY, now()->toIso8601String(), $ttl);
    }

    public function clear(): void
    {
        Cache::forget(self::ENDPOINTS_KEY);
        Cache::forget(self::SCANNED_AT_KEY);
    }

    public function scannedAt(): ?Carbon
    {
        $value = Cache::get(self::SCANNED_AT_KEY);

        return $value ? Carbon::parse($value) : null;
    }
}
