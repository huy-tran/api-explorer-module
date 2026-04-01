<?php

use Illuminate\Support\Facades\Storage;
use Modules\ApiExplorer\Actions\Environment\ExportEnvironment;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->action = app(ExportEnvironment::class);
});

it('returns the raw markdown contents of an environment', function (): void {
    Storage::disk('local')->put('api-explorer/environments/local.md', "baseUrl: https://app.test\n\nvars {\n  token: abc\n}");

    $contents = $this->action->handle('local');

    expect($contents)->toContain('baseUrl: https://app.test')
        ->and($contents)->toContain('token: abc');
});

it('returns 404 when the environment does not exist', function (): void {
    $this->action->handle('nonexistent');
})->throws(HttpException::class);

it('rejects an invalid environment name', function (): void {
    $this->action->handle('../evil');
})->throws(HttpException::class);
