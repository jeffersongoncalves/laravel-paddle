<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('creates a refund adjustment', function () {
    Http::fake(['*/adjustments' => Http::response(['data' => ['id' => 'adj_123']], 201)]);

    Paddle::adjustments()->create('txn_123', 'refund', 'Customer request', [
        ['item_id' => 'txnitm_123', 'type' => 'full'],
    ]);

    Http::assertSent(fn ($request) => $request['transaction_id'] === 'txn_123'
        && $request['action'] === 'refund'
        && $request['reason'] === 'Customer request'
        && $request['items'][0]['item_id'] === 'txnitm_123');
});

it('requires every adjustment field', function () {
    Paddle::adjustments()->create('txn_123', 'refund', 'Customer request', []);
})->throws(InvalidArgumentException::class, '"transactionId", "action", "reason" and "items" are all required.');

it('lists adjustments and fetches a credit note', function () {
    Http::fake([
        '*/adjustments/adj_123/credit-note' => Http::response(['data' => ['url' => 'https://paddle.test/cn.pdf']]),
        '*/adjustments*' => Http::response(['data' => []]),
    ]);

    Paddle::adjustments()->list(['status' => 'approved']);

    expect(Paddle::adjustments()->creditNote('adj_123')['data']['url'])->toBe('https://paddle.test/cn.pdf');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'status=approved'));
});
