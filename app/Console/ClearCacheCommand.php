<?php

namespace Modules\ApiExplorer\Console;

use Illuminate\Console\Command;
use Modules\ApiExplorer\Cache\ExplorerCache;

class ClearCacheCommand extends Command
{
    protected $signature = 'api-explorer:clear';

    protected $description = 'Clear the API Explorer route scan cache.';

    public function __construct(
        private readonly ExplorerCache $cache,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->cache->clear();
        $this->line('API Explorer cache cleared.');

        return self::SUCCESS;
    }
}
