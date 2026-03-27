<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEnvironment
{
    use AsAction;

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

    private function validateName(string $name): void
    {
        abort_if(
            basename($name) !== $name || ! preg_match('/^[a-zA-Z0-9_\-\[\] ]+$/', $name),
            400,
            'Invalid environment name.'
        );
    }
}
