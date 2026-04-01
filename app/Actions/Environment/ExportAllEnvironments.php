<?php

namespace Modules\ApiExplorer\Actions\Environment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExportAllEnvironments
{
    use AsAction;

    /**
     * Return all environment files as a map of filename => contents.
     *
     * @return array<string, string>
     */
    public function handle(): array
    {
        $files = Storage::disk('local')->files('api-explorer/environments');

        $environments = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.md')) {
                $environments[basename($file)] = Storage::disk('local')->get($file) ?? '';
            }
        }

        return $environments;
    }

    public function asController(ActionRequest $request): StreamedResponse|JsonResponse
    {
        $environments = $this->handle();

        if (empty($environments)) {
            return response()->json(['error' => 'No environments to export.'], 422);
        }

        $tmpPath = sys_get_temp_dir().'/api-explorer-environments-'.uniqid().'.zip';

        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($environments as $filename => $contents) {
            $zip->addFromString($filename, $contents);
        }

        $zip->close();

        return response()->streamDownload(static function () use ($tmpPath): void {
            readfile($tmpPath);
            unlink($tmpPath);
        }, 'environments.zip', ['Content-Type' => 'application/zip']);
    }
}
