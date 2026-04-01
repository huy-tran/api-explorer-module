<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ApiExplorer\Data\EnvironmentData;
use Modules\ApiExplorer\Services\EnvironmentParser;
use ZipArchive;

class ImportEnvironment
{
    use AsAction;

    public function __construct(
        private readonly EnvironmentParser $parser,
        private readonly SaveEnvironment $saveEnvironment,
    ) {}

    /**
     * Import environments from an array of file entries.
     *
     * @param  array<int, array{name: string, contents: string}>  $files
     * @return array{imported: array<int, string>, skipped: array<int, string>}
     */
    public function handle(array $files): array
    {
        $imported = [];
        $skipped = [];

        foreach ($files as ['name' => $name, 'contents' => $contents]) {
            if (basename($name) !== $name || ! preg_match('/^[a-zA-Z0-9_\-\[\] ]+$/', $name)) {
                $skipped[] = $name;

                continue;
            }

            $baseUrl = $this->parser->parseBaseUrl($contents);
            $vars = $this->parser->parse($contents);

            $this->saveEnvironment->handle(new EnvironmentData($name, $baseUrl, $vars));

            $imported[] = $name;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:md,zip', 'max:2048'],
        ];
    }

    public function asController(ActionRequest $request): JsonResponse
    {
        $files = $this->extractFiles($request->file('file'));

        return response()->json($this->handle($files));
    }

    /**
     * @return array<int, array{name: string, contents: string}>
     */
    private function extractFiles(UploadedFile $file): array
    {
        if (strtolower($file->getClientOriginalExtension()) === 'zip') {
            return $this->extractFromZip($file);
        }

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return [['name' => $name, 'contents' => file_get_contents($file->getRealPath())]];
    }

    /**
     * @return array<int, array{name: string, contents: string}>
     */
    private function extractFromZip(UploadedFile $file): array
    {
        $files = [];
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            return [];
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (! str_ends_with($filename, '.md')) {
                continue;
            }

            $contents = $zip->getFromIndex($i);

            if ($contents !== false) {
                $files[] = ['name' => basename($filename, '.md'), 'contents' => $contents];
            }
        }

        $zip->close();

        return $files;
    }
}
