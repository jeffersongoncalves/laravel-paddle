<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;

class Products extends Resource
{
    protected string $path = '/products';

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): array
    {
        if (empty($attributes['name'])) {
            throw new InvalidArgumentException('The "name" attribute is required.');
        }

        if (empty($attributes['tax_category'])) {
            throw new InvalidArgumentException('The "tax_category" attribute is required (e.g. standard, digital-goods, saas).');
        }

        return $this->client->post($this->path, $attributes);
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
