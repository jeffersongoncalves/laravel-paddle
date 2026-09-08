<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Exceptions\PaddleException;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('lists products with the default per_page and a bearer token', function () {
    Http::fake(['*/products*' => Http::response(['data' => [['id' => 'pro_123']]])]);

    $result = Paddle::products()->list();

    expect($result['data'][0]['id'])->toBe('pro_123');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'per_page=50')
        && $request->hasHeader('Authorization', 'Bearer test-api-key'));
});

it('gets a single product', function () {
    Http::fake(['*/products/pro_123' => Http::response(['data' => ['id' => 'pro_123']])]);

    expect(Paddle::products()->get('pro_123')['data']['id'])->toBe('pro_123');
});

it('creates a product', function () {
    Http::fake(['*/products' => Http::response(['data' => ['id' => 'pro_123']], 201)]);

    Paddle::products()->create(['name' => 'Pro Plan', 'tax_category' => 'saas']);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['name'] === 'Pro Plan'
        && $request['tax_category'] === 'saas');
});

it('requires a name to create a product', function () {
    Paddle::products()->create(['tax_category' => 'saas']);
})->throws(InvalidArgumentException::class, 'The "name" attribute is required.');

it('requires a tax_category to create a product', function () {
    Paddle::products()->create(['name' => 'Pro Plan']);
})->throws(InvalidArgumentException::class, 'The "tax_category" attribute is required (e.g. standard, digital-goods, saas).');

it('updates a product with PATCH', function () {
    Http::fake(['*/products/pro_123' => Http::response(['data' => []])]);

    Paddle::products()->update('pro_123', ['name' => 'Renamed']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['name'] === 'Renamed');
});

it('archives a product', function () {
    Http::fake(['*/products/pro_123' => Http::response(['data' => []])]);

    Paddle::products()->archive('pro_123');

    Http::assertSent(fn ($request) => $request['status'] === 'archived');
});

it('throws a PaddleException carrying the Paddle error code', function () {
    Http::fake(['*/products/pro_404' => Http::response([
        'error' => ['code' => 'entity_not_found', 'detail' => 'Entity not found'],
    ], 404)]);

    try {
        Paddle::products()->get('pro_404');
    } catch (PaddleException $e) {
        expect($e->getMessage())->toBe('Entity not found')
            ->and($e->errorCode())->toBe('entity_not_found')
            ->and($e->getCode())->toBe(404);

        return;
    }

    $this->fail('No PaddleException was thrown.');
});
