<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('lists events', function () {
    Http::fake(['*/events*' => Http::response(['data' => [['event_type' => 'subscription.created']]])]);

    expect(Paddle::events()->list()['data'][0]['event_type'])->toBe('subscription.created');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'per_page=50'));
});

it('lists event types', function () {
    Http::fake(['*/event-types' => Http::response(['data' => [['name' => 'transaction.completed']]])]);

    expect(Paddle::events()->types()['data'][0]['name'])->toBe('transaction.completed');
});
