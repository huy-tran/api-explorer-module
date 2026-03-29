<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Actions\Environment\Concerns\ValidatesEnvironmentName;

class DeleteEnvironment
{
    use AsAction;
    use ValidatesEnvironmentName;

    /**
     * Delete an environment by name.
     */
    public function handle(string $name): void
    {
        $this->validateName($name);

        $path = "api-explorer/environments/{$name}.md";
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function asController(ActionRequest $request, string $name): JsonResponse
    {
        $this->handle($name);

        return response()->json(['deleted' => true]);
    }
}
