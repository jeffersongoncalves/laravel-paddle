<?php

namespace JeffersonGoncalves\Paddle\Resources;

class Subscriptions extends Resource
{
    protected string $path = '/subscriptions';

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->patch($this->path.'/'.$id, $attributes);
    }

    public function cancel(string $id, string $effectiveFrom = 'next_billing_period'): array
    {
        return $this->client->post($this->path.'/'.$id.'/cancel', ['effective_from' => $effectiveFrom]);
    }

    /** @param string|null $resumeAt ISO-8601 date; null pauses indefinitely. */
    public function pause(string $id, ?string $resumeAt = null): array
    {
        return $this->client->post(
            $this->path.'/'.$id.'/pause',
            $resumeAt === null ? [] : ['resume_at' => $resumeAt],
        );
    }

    public function resume(string $id, string $effectiveFrom = 'immediately'): array
    {
        return $this->client->post($this->path.'/'.$id.'/resume', ['effective_from' => $effectiveFrom]);
    }

    public function activate(string $id): array
    {
        return $this->client->post($this->path.'/'.$id.'/activate');
    }

    /**
     * One-off charge on top of a subscription.
     *
     * @param  array<int, array<string, mixed>>  $items  Each item: ['price_id' => ..., 'quantity' => ...]
     */
    public function charge(string $id, array $items, string $effectiveFrom = 'next_billing_period'): array
    {
        return $this->client->post($this->path.'/'.$id.'/charge', [
            'items' => $items,
            'effective_from' => $effectiveFrom,
        ]);
    }

    /** Transaction used to let the customer update their payment method. */
    public function updatePaymentMethodTransaction(string $id): array
    {
        return $this->client->get($this->path.'/'.$id.'/update-payment-method-transaction');
    }
}
