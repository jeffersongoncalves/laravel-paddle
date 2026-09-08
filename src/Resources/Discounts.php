<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;

class Discounts extends Resource
{
    protected string $path = '/discounts';

    /**
     * @param  string  $type  flat, flat_per_seat or percentage
     * @param  array<string, mixed>  $attributes  Extra Paddle fields: code, currency_code, expires_at, restrict_to, ...
     */
    public function create(int|string $amount, string $type, string $description = 'Discount', array $attributes = []): array
    {
        if ((string) $amount === '' || $type === '') {
            throw new InvalidArgumentException('Both "amount" and "type" are required.');
        }

        return $this->client->post($this->path, array_merge([
            'amount' => (string) $amount,
            'type' => $type,
            'description' => $description,
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
