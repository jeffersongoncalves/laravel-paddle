<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('creates a transaction', function () {
    Http::fake(['*/transactions' => Http::response(['data' => ['id' => 'txn_123']], 201)]);

    Paddle::transactions()->create(
        [['price_id' => 'pri_123', 'quantity' => 1]],
        ['customer_id' => 'ctm_123'],
    );

    Http::assertSent(fn ($request) => $request['items'][0]['price_id'] === 'pri_123'
        && $request['customer_id'] === 'ctm_123');
});

it('requires at least one item', function () {
    Paddle::transactions()->create([]);
})->throws(InvalidArgumentException::class, 'The "items" array must not be empty.');

it('lists transactions with filters', function () {
    Http::fake(['*/transactions*' => Http::response(['data' => []])]);

    Paddle::transactions()->list(['status' => 'billed']);

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'status=billed'));
});

it('updates a transaction and fetches its invoice', function () {
    Http::fake([
        '*/transactions/txn_123/invoice' => Http::response(['data' => ['url' => 'https://paddle.test/invoice.pdf']]),
        '*/transactions/txn_123' => Http::response(['data' => []]),
    ]);

    Paddle::transactions()->update('txn_123', ['status' => 'canceled']);

    expect(Paddle::transactions()->invoice('txn_123')['data']['url'])->toBe('https://paddle.test/invoice.pdf');
    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['status'] === 'canceled');
});
