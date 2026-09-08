<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('creates a customer', function () {
    Http::fake(['*/customers' => Http::response(['data' => ['id' => 'ctm_123']], 201)]);

    Paddle::customers()->create('jane@example.com', ['name' => 'Jane']);

    Http::assertSent(fn ($request) => $request['email'] === 'jane@example.com' && $request['name'] === 'Jane');
});

it('requires an email to create a customer', function () {
    Paddle::customers()->create('');
})->throws(InvalidArgumentException::class, 'The "email" attribute is required.');

it('lists, gets, updates and archives a customer', function () {
    Http::fake([
        '*/customers/ctm_123' => Http::response(['data' => ['id' => 'ctm_123']]),
        '*/customers*' => Http::response(['data' => []]),
    ]);

    Paddle::customers()->list();
    expect(Paddle::customers()->get('ctm_123')['data']['id'])->toBe('ctm_123');
    Paddle::customers()->update('ctm_123', ['name' => 'Janet']);
    Paddle::customers()->archive('ctm_123');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && ($request['name'] ?? null) === 'Janet');
    Http::assertSent(fn ($request) => ($request['status'] ?? null) === 'archived');
});

it('reads a customer credit balance', function () {
    Http::fake(['*/customers/ctm_123/credit-balances' => Http::response(['data' => [['currency_code' => 'USD']]])]);

    expect(Paddle::customers()->creditBalances('ctm_123')['data'][0]['currency_code'])->toBe('USD');
});
