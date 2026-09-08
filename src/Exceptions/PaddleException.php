<?php

namespace JeffersonGoncalves\Paddle\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class PaddleException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $errorBody = [];

    public static function fromResponse(Response $response): self
    {
        $body = (array) ($response->json() ?? []);

        $message = $body['error']['detail']
            ?? $body['error']['code']
            ?? "Paddle API error (HTTP {$response->status()}).";

        $exception = new self((string) $message, $response->status());
        $exception->errorBody = $body;

        return $exception;
    }

    /** @return array<string, mixed> */
    public function errorBody(): array
    {
        return $this->errorBody;
    }

    /** Paddle's machine-readable error code, e.g. "entity_not_found". */
    public function errorCode(): ?string
    {
        $code = $this->errorBody['error']['code'] ?? null;

        return is_string($code) ? $code : null;
    }
}
