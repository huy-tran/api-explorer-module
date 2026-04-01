<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Actions\Environment\Concerns\ValidatesEnvironmentName;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportEnvironment
{
    use AsAction;
    use ValidatesEnvironmentName;

    /**
     * Return the raw markdown contents of the environment file.
     */
    public function handle(string $name): string
    {
        $this->validateName($name);

        $path = "api-explorer/environments/{$name}.md";

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->get($path) ?? '';
    }

    public function asController(ActionRequest $request, string $name): StreamedResponse
    {
        $contents = $this->handle($name);

        return response()->streamDownload(
            static fn () => print ($contents),
            "{$name}.md",
            ['Content-Type' => 'text/markdown'],
        );
    }
}
