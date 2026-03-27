<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Data\EnvironmentData;
use Modules\ApiExplorer\Services\EnvironmentParser;

class GetEnvironment
{
    use AsAction;

    public function __construct(
        private readonly EnvironmentParser $parser,
    ) {}

    /**
     * Load an environment by name.
     */
    public function handle(string $name): EnvironmentData
    {
        $this->validateName($name);

        $path = "api-explorer/environments/{$name}.md";

        abort_unless(Storage::disk('local')->exists($path), 404);

        $contents = Storage::disk('local')->get($path);
        $baseUrl = $this->parser->parseBaseUrl($contents);
        $vars = $this->parser->parse($contents);

        return new EnvironmentData($name, $baseUrl, $vars);
    }

    public function asController(ActionRequest $request, string $name): JsonResponse
    {
        return response()->json($this->handle($name));
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
