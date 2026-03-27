<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListEnvironments
{
    use AsAction;

    /**
     * List all environment names from the environments directory.
     *
     * @return array<int, string>
     */
    public function handle(): array
    {
        $files = Storage::disk('local')->files('api-explorer/environments');

        $names = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.md')) {
                // Extract name: api-explorer/environments/local.md → local
                $name = basename($file, '.md');
                $names[] = $name;
            }
        }

        return sort($names) ? $names : [];
    }

    public function asController(ActionRequest $request): JsonResponse
    {
        return response()->json($this->handle());
    }
}
