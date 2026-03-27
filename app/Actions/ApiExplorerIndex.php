<?php

namespace Modules\ApiExplorer\Actions;

use Illuminate\View\View;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Cache\ExplorerCache;
use Modules\ApiExplorer\Services\EndpointPipeline;

class ApiExplorerIndex
{
    use AsAction;

    public function __construct(
        private readonly ExplorerCache $cache,
        private readonly EndpointPipeline $pipeline,
    ) {}

    public function asController(ActionRequest $request): View
    {
        $endpoints = $this->cache->get();

        if ($endpoints === null) {
            $endpoints = $this->pipeline->run();
            $this->cache->put($endpoints);
        }

        return view('api-explorer::explorer', [
            'endpoints' => $endpoints,
            'scannedAt' => $this->cache->scannedAt(),
            'cacheEnabled' => $this->cache->isEnabled(),
            'appUrl' => config('app.url'),
        ]);
    }
}
