<?php

use Illuminate\Support\Facades\Storage;
use Modules\ApiExplorer\Actions\Environment\ExportAllEnvironments;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->action = app(ExportAllEnvironments::class);
});

it('returns a map of filename to contents for all environments', function (): void {
    Storage::disk('local')->put('api-explorer/environments/local.md', 'baseUrl: https://app.test');
    Storage::disk('local')->put('api-explorer/environments/staging.md', 'baseUrl: https://staging.test');

    $result = $this->action->handle();

    expect($result)->toHaveKeys(['local.md', 'staging.md'])
        ->and($result['local.md'])->toContain('https://app.test')
        ->and($result['staging.md'])->toContain('https://staging.test');
});

it('returns an empty array when no environments exist', function (): void {
    $result = $this->action->handle();

    expect($result)->toBeEmpty();
});

it('ignores non-markdown files in the environments directory', function (): void {
    Storage::disk('local')->put('api-explorer/environments/local.md', 'baseUrl: https://app.test');
    Storage::disk('local')->put('api-explorer/environments/notes.txt', 'some text');

    $result = $this->action->handle();

    expect($result)->toHaveKey('local.md')
        ->and($result)->not->toHaveKey('notes.txt');
});
