<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;

class Transactions extends Resource
{
    protected string $path = '/transactions';

    /**
     * @param  array<int, array<string, mixed>>  $items  Each item: ['price_id' => ..., 'quantity' => ...]
     * @param  array<string, mixed>  $attributes  Extra Paddle fields: customer_id, collection_mode, discount_id, ...
     */
    public function create(array $items, array $attributes = []): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('The "items" array must not be empty.');
        }

        return $this->client->post($this->path, array_merge(['items' => $items], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->patch($this->path.'/'.$id, $attributes);
    }

    /** Signed URL to the transaction's invoice PDF. */
    public function invoice(string $id): array
    {
        return $this->client->get($this->path.'/'.$id.'/invoice');
    }
}
