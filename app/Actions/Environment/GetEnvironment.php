<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Actions\Environment\Concerns\ValidatesEnvironmentName;
use Modules\ApiExplorer\Data\EnvironmentData;
use Modules\ApiExplorer\Services\EnvironmentParser;

class GetEnvironment
{
    use AsAction;
    use ValidatesEnvironmentName;

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

        $contents = Storage::disk('local')->get($path) ?? '';
        $baseUrl = $this->parser->parseBaseUrl($contents);
        $vars = $this->parser->parse($contents);

        return new EnvironmentData($name, $baseUrl, $vars);
    }

    public function asController(ActionRequest $request, string $name): JsonResponse
    {
        return response()->json($this->handle($name));
    }
}
