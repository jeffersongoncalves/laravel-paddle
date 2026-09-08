<?php

namespace JeffersonGoncalves\Paddle\Resources;

use JeffersonGoncalves\Paddle\PaddleClient;

/**
 * Shared list/get behaviour for the Paddle entities that expose both a
 * collection endpoint and a GET by id (everything but adjustments and events).
 */
abstract class Resource
{
    /** Collection path, e.g. "/products". */
    protected string $path;

    public function __construct(
        protected PaddleClient $client,
        protected int $defaultPerPage = 50,
    ) {}

    /**
     * @param  array<string, mixed>  $params  Paddle filters: status, after, per_page, order_by, id, ...
     */
    public function list(array $params = []): array
    {
        return $this->client->get($this->path, array_merge(['per_page' => $this->defaultPerPage], $params));
    }

    public function get(string $id): array
    {
        return $this->client->get($this->path.'/'.$id);
    }
}
