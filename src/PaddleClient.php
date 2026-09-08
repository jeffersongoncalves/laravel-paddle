<?php

namespace JeffersonGoncalves\Paddle;

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Paddle\Exceptions\PaddleException;

/**
 * Thin wrapper around Laravel's Http client for the Paddle Billing API v1.
 *
 * Paddle authenticates with a Bearer API key and updates every resource with
 * PATCH, never PUT.
 *
 * ponytail: Http::retry() already covers transient-failure retries if ever
 * needed later — no custom retry/backoff layer built here speculatively.
 */
class PaddleClient
{
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl = 'https://api.paddle.com',
    ) {}

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    /** @param array<string, mixed> $body */
    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, $body);
    }

    /** @param array<string, mixed> $body */
    public function patch(string $path, array $body = []): array
    {
        return $this->request('patch', $path, $body);
    }

    /** @param array<string, mixed> $data */
    protected function request(string $method, string $path, array $data = []): array
    {
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->baseUrl(rtrim($this->baseUrl, '/'))
            ->{$method}($path, $data);

        if ($response->failed()) {
            throw PaddleException::fromResponse($response);
        }

        return (array) ($response->json() ?? []);
    }
}
