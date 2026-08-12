<?php

use Illuminate\Support\Facades\File;
use OpenApi\Generator;

it('serves the swagger ui', function () {
    $this->get('/api/documentation')
        ->assertOk()
        ->assertSee('swagger', false);
});

it('builds a valid openapi document covering every documented endpoint', function () {
    $openApi = (new Generator)->generate([base_path('app')]);

    expect($openApi)->not->toBeNull();

    $documented = collect($openApi->paths)->map(fn ($path): string => $path->path)->all();

    expect($documented)->toContain(
        '/v1/auth/login',
        '/v1/customers',
        '/v1/customers/{customer}',
        '/v1/customers/{customer}/services',
        '/v1/services',
        '/v1/services/{service}',
    );

    $schemes = collect($openApi->components->securitySchemes)
        ->map(fn ($scheme): string => $scheme->securityScheme)
        ->all();

    expect($schemes)->toContain('basicAuth', 'bearerAuth');
});

it('declares every documented path from the App\OpenApi namespace alone', function () {
    $paths = fn (string $directory): array => collect((new Generator)->generate([$directory])->paths)
        ->map(fn ($path): string => $path->path)
        ->sort()
        ->values()
        ->all();

    expect($paths(app_path('OpenApi')))->toBe($paths(base_path('app')))
        ->toHaveCount(9);
});

it('keeps the annotations out of the controllers, resources and requests', function () {
    $annotated = collect(File::allFiles(app_path('Http')))
        ->filter(fn ($file): bool => str_contains((string) file_get_contents($file->getPathname()), 'OpenApi\Attributes'))
        ->map(fn ($file): string => $file->getRelativePathname())
        ->all();

    expect($annotated)->toBeEmpty();
});
