<?php

use Illuminate\Http\Client\Response;
use JeffersonGoncalves\Paddle\Exceptions\PaddleException;

function fakePaddleResponse(int $status, array $body): Response
{
    return new Response(new GuzzleHttp\Psr7\Response($status, [], json_encode($body)));
}

it('builds the message from the Paddle error detail', function () {
    $exception = PaddleException::fromResponse(fakePaddleResponse(404, [
        'error' => ['code' => 'entity_not_found', 'detail' => 'Entity not found'],
    ]));

    expect($exception->getMessage())->toBe('Entity not found')
        ->and($exception->getCode())->toBe(404)
        ->and($exception->errorCode())->toBe('entity_not_found');
});

it('falls back to the error code when there is no detail', function () {
    $exception = PaddleException::fromResponse(fakePaddleResponse(403, [
        'error' => ['code' => 'forbidden'],
    ]));

    expect($exception->getMessage())->toBe('forbidden');
});

it('falls back to a generic message on an empty body', function () {
    $exception = PaddleException::fromResponse(fakePaddleResponse(500, []));

    expect($exception->getMessage())->toBe('Paddle API error (HTTP 500).')
        ->and($exception->errorCode())->toBeNull();
});
