<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Facades\Paddle;

it('lists and gets notifications', function () {
    Http::fake([
        '*/notifications/ntf_123' => Http::response(['data' => ['id' => 'ntf_123']]),
        '*/notifications*' => Http::response(['data' => []]),
    ]);

    Paddle::notifications()->list(['status' => 'failed']);

    expect(Paddle::notifications()->get('ntf_123')['data']['id'])->toBe('ntf_123');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'status=failed'));
});

it('replays a notification', function () {
    Http::fake(['*/notifications/ntf_123/replay' => Http::response(['data' => ['id' => 'ntf_456']], 201)]);

    Paddle::notifications()->replay('ntf_123');

    Http::assertSent(fn ($request) => $request->method() === 'POST');
});

it('reads a notification delivery log', function () {
    Http::fake(['*/notifications/ntf_123/logs*' => Http::response(['data' => [['response_code' => 500]]])]);

    expect(Paddle::notifications()->logs('ntf_123')['data'][0]['response_code'])->toBe(500);
});
