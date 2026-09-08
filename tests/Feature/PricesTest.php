<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('lists prices', function () {
    Http::fake(['*/prices*' => Http::response(['data' => [['id' => 'pri_123']]])]);

    expect(Paddle::prices()->list(['per_page' => 10])['data'][0]['id'])->toBe('pri_123');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'per_page=10'));
});

it('creates a price with a unit_price payload', function () {
    Http::fake(['*/prices' => Http::response(['data' => ['id' => 'pri_123']], 201)]);

    Paddle::prices()->create('pro_123', 1990, 'BRL', 'Monthly', [
        'billing_cycle' => ['interval' => 'month', 'frequency' => 1],
    ]);

    Http::assertSent(fn ($request) => $request['product_id'] === 'pro_123'
        && $request['unit_price'] === ['amount' => '1990', 'currency_code' => 'BRL']
        && $request['description'] === 'Monthly'
        && $request['billing_cycle']['interval'] === 'month');
});

it('requires a product id to create a price', function () {
    Paddle::prices()->create('', 1990);
})->throws(InvalidArgumentException::class, 'Both "productId" and "amount" are required.');

it('updates and archives a price', function () {
    Http::fake(['*/prices/pri_123' => Http::response(['data' => []])]);

    Paddle::prices()->update('pri_123', ['description' => 'Yearly']);
    Paddle::prices()->archive('pri_123');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && ($request['description'] ?? null) === 'Yearly');
    Http::assertSent(fn ($request) => ($request['status'] ?? null) === 'archived');
});
