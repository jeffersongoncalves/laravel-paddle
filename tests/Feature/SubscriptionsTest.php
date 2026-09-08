<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('cancels a subscription at the end of the billing period by default', function () {
    Http::fake(['*/subscriptions/sub_123/cancel' => Http::response(['data' => []])]);

    Paddle::subscriptions()->cancel('sub_123');

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['effective_from'] === 'next_billing_period');
});

it('pauses indefinitely when no resume date is given', function () {
    Http::fake(['*/subscriptions/sub_123/pause' => Http::response(['data' => []])]);

    Paddle::subscriptions()->pause('sub_123');

    Http::assertSent(fn ($request) => $request->data() === []);
});

it('pauses until a resume date', function () {
    Http::fake(['*/subscriptions/sub_123/pause' => Http::response(['data' => []])]);

    Paddle::subscriptions()->pause('sub_123', '2026-12-01T00:00:00Z');

    Http::assertSent(fn ($request) => $request['resume_at'] === '2026-12-01T00:00:00Z');
});

it('resumes immediately by default', function () {
    Http::fake(['*/subscriptions/sub_123/resume' => Http::response(['data' => []])]);

    Paddle::subscriptions()->resume('sub_123');

    Http::assertSent(fn ($request) => $request['effective_from'] === 'immediately');
});

it('updates a subscription with PATCH', function () {
    Http::fake(['*/subscriptions/sub_123' => Http::response(['data' => []])]);

    Paddle::subscriptions()->update('sub_123', ['proration_billing_mode' => 'prorated_immediately']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['proration_billing_mode'] === 'prorated_immediately');
});

it('charges a subscription one-off items', function () {
    Http::fake(['*/subscriptions/sub_123/charge' => Http::response(['data' => []])]);

    Paddle::subscriptions()->charge('sub_123', [['price_id' => 'pri_123', 'quantity' => 2]], 'immediately');

    Http::assertSent(fn ($request) => $request['items'][0]['price_id'] === 'pri_123'
        && $request['effective_from'] === 'immediately');
});

it('activates a trialing subscription and fetches the payment-method transaction', function () {
    Http::fake([
        '*/subscriptions/sub_123/activate' => Http::response(['data' => []]),
        '*/subscriptions/sub_123/update-payment-method-transaction' => Http::response(['data' => ['id' => 'txn_123']]),
    ]);

    Paddle::subscriptions()->activate('sub_123');

    expect(Paddle::subscriptions()->updatePaymentMethodTransaction('sub_123')['data']['id'])->toBe('txn_123');
});
