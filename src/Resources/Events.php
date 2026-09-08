<?php

namespace JeffersonGoncalves\Paddle\Resources;

use JeffersonGoncalves\Paddle\PaddleClient;

/**
 * The event stream and the catalog of event types.
 *
 * ponytail: not a Resource subclass — Paddle exposes no GET /events/{id}.
 */
class Events
{
    public function __construct(
        protected PaddleClient $client,
        protected int $defaultPerPage = 50,
    ) {}

    /** @param array<string, mixed> $params */
    public function list(array $params = []): array
    {
        return $this->client->get('/events', array_merge(['per_page' => $this->defaultPerPage], $params));
    }

    public function types(): array
    {
        return $this->client->get('/event-types');
    }
}
