<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;

class Customers extends Resource
{
    protected string $path = '/customers';

    /** @param array<string, mixed> $attributes */
    public function create(string $email, array $attributes = []): array
    {
        if ($email === '') {
            throw new InvalidArgumentException('The "email" attribute is required.');
        }

        return $this->client->post($this->path, array_merge(['email' => $email], $attributes));
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

    public function creditBalances(string $id): array
    {
        return $this->client->get($this->path.'/'.$id.'/credit-balances');
    }
}
