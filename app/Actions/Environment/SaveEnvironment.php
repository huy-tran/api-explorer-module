<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Data\EnvironmentData;
use Modules\ApiExplorer\Services\EnvironmentParser;

class SaveEnvironment
{
    use AsAction;

    public function __construct(
        private readonly EnvironmentParser $parser,
    ) {}

    /**
     * Save or update an environment.
     * If oldName differs from data.name, delete the old file (rename).
     */
    public function handle(EnvironmentData $data, ?string $oldName = null): EnvironmentData
    {
        $this->validateName($data->name);

        // If renaming, delete the old file
        if ($oldName && $oldName !== $data->name) {
            $oldPath = "api-explorer/environments/{$oldName}.md";
            if (Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        $path = "api-explorer/environments/{$data->name}.md";
        $contents = $this->parser->format($data->baseUrl, $data->vars);
        Storage::disk('local')->put($path, $contents);

        return $data;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z0-9_\-\[\] ]+$/'],
            'baseUrl' => ['nullable', 'string'],
            'vars' => ['required', 'array'],
            'vars.*' => ['string'],
        ];
    }

    public function asController(ActionRequest $request, ?string $name = null): JsonResponse
    {
        $data = EnvironmentData::from($request->validated());

        return response()->json($this->handle($data, $name));
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
