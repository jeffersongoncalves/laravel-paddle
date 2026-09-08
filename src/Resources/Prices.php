<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;

class Prices extends Resource
{
    protected string $path = '/prices';

    /**
     * @param  array<string, mixed>  $attributes  Extra Paddle fields: billing_cycle, trial_period, quantity, ...
     */
    public function create(
        string $productId,
        int|string $amount,
        string $currencyCode = 'USD',
        string $description = 'Price',
        array $attributes = [],
    ): array {
        if ($productId === '' || (string) $amount === '') {
            throw new InvalidArgumentException('Both "productId" and "amount" are required.');
        }

        return $this->client->post($this->path, array_merge([
            'product_id' => $productId,
            'description' => $description,
            'unit_price' => ['amount' => (string) $amount, 'currency_code' => $currencyCode],
        ], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->patch($this->path.'/'.$id, $attributes);
    }

    public function archive(string $id): array
    {
        return $this->update($id, ['status' => 'archived']);
    }
}
