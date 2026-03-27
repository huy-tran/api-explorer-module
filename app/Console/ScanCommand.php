<?php

namespace Modules\ApiExplorer\Console;

use Illuminate\Console\Command;
use Modules\ApiExplorer\Cache\ExplorerCache;
use Modules\ApiExplorer\Services\EndpointPipeline;

class ScanCommand extends Command
{
    protected $signature = 'api-explorer:scan';

    protected $description = 'Scan all API routes and DTOs, warming the API Explorer cache.';

    public function __construct(
        private readonly ExplorerCache $cache,
        private readonly EndpointPipeline $pipeline,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->cache->clear();
        $this->line('Scanning routes and DTOs...');

        $endpoints = $this->pipeline->run();
        $grouped = collect($endpoints)->groupBy('module');

        foreach ($grouped as $module => $moduleEndpoints) {
            $this->line("  {$module}: ".$moduleEndpoints->count().' endpoint(s)');
        }

        $this->cache->put($endpoints);
        $this->line('Cache warmed successfully.');

        return self::SUCCESS;
    }
}
