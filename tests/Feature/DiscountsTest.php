<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('creates a discount', function () {
    Http::fake(['*/discounts' => Http::response(['data' => ['id' => 'dsc_123']], 201)]);

    Paddle::discounts()->create(25, 'percentage', 'Launch', ['code' => 'LAUNCH25']);

    Http::assertSent(fn ($request) => $request['amount'] === '25'
        && $request['type'] === 'percentage'
        && $request['description'] === 'Launch'
        && $request['code'] === 'LAUNCH25');
});

it('requires an amount and a type', function () {
    Paddle::discounts()->create('', 'percentage');
})->throws(InvalidArgumentException::class, 'Both "amount" and "type" are required.');

it('lists, gets, updates and archives a discount', function () {
    Http::fake([
        '*/discounts/dsc_123' => Http::response(['data' => ['id' => 'dsc_123']]),
        '*/discounts*' => Http::response(['data' => []]),
    ]);

    Paddle::discounts()->list();
    expect(Paddle::discounts()->get('dsc_123')['data']['id'])->toBe('dsc_123');
    Paddle::discounts()->update('dsc_123', ['description' => 'Updated']);
    Paddle::discounts()->archive('dsc_123');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && ($request['description'] ?? null) === 'Updated');
    Http::assertSent(fn ($request) => ($request['status'] ?? null) === 'archived');
});
