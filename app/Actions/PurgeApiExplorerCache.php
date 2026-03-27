<?php

namespace Modules\ApiExplorer\Actions;

use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Cache\ExplorerCache;

class PurgeApiExplorerCache
{
    use AsAction;

    public function __construct(
        private readonly ExplorerCache $cache,
    ) {}

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->cache->clear();

        return redirect()->route('api-explorer.index');
    }
}
