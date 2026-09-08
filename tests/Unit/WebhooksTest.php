<?php

use Illuminate\Http\Request;
use JeffersonGoncalves\Paddle\Facades\Paddle;

function paddleSignature(string $payload, string $secret = 'test-secret', ?int $timestamp = null): string
{
    $timestamp ??= time();

    return 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$payload, $secret);
}

it('accepts a signature built with the configured secret', function () {
    $payload = '{"event_type":"transaction.completed"}';

    expect(Paddle::webhooks()->verify($payload, paddleSignature($payload)))->toBeTrue();
});

it('rejects a signature made with the wrong secret', function () {
    $payload = '{"event_type":"transaction.completed"}';

    expect(Paddle::webhooks()->verify($payload, paddleSignature($payload, 'other-secret')))->toBeFalse();
});

it('rejects a tampered payload', function () {
    $signature = paddleSignature('{"amount":"100"}');

    expect(Paddle::webhooks()->verify('{"amount":"999"}', $signature))->toBeFalse();
});

it('rejects a malformed signature header', function () {
    expect(Paddle::webhooks()->verify('{}', 'nonsense'))->toBeFalse();
});

it('rejects a stale timestamp but accepts it when the age check is disabled', function () {
    $payload = '{}';
    $signature = paddleSignature($payload, 'test-secret', time() - 60);

    expect(Paddle::webhooks()->verify($payload, $signature))->toBeFalse()
        ->and(Paddle::webhooks()->verify($payload, $signature, maxAge: 0))->toBeTrue();
});

it('verifies straight off a request', function () {
    $payload = '{"event_type":"subscription.canceled"}';

    $request = Request::create('/paddle/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('Paddle-Signature', paddleSignature($payload));

    expect(Paddle::webhooks()->verifyRequest($request))->toBeTrue();
});

it('refuses to verify when no secret is configured', function () {
    config(['paddle.webhook_secret' => null]);

    Paddle::webhooks()->verify('{}', paddleSignature('{}'));
})->throws(InvalidArgumentException::class, 'No Paddle webhook secret configured. Set PADDLE_WEBHOOK_SECRET.');
