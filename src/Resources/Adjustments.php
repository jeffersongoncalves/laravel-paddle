<?php

namespace JeffersonGoncalves\Paddle\Resources;

use InvalidArgumentException;
use JeffersonGoncalves\Paddle\PaddleClient;

/**
 * Refunds, credits and chargebacks.
 *
 * ponytail: not a Resource subclass — Paddle exposes no GET /adjustments/{id}.
 */
class Adjustments
{
    public function __construct(
        protected PaddleClient $client,
        protected int $defaultPerPage = 50,
    ) {}

    /** @param array<string, mixed> $params */
    public function list(array $params = []): array
    {
        return $this->client->get('/adjustments', array_merge(['per_page' => $this->defaultPerPage], $params));
    }

    /**
     * @param  string  $action  refund, credit or chargeback
     * @param  array<int, array<string, mixed>>  $items  Each item: ['item_id' => ..., 'type' => ..., 'amount' => ...]
     */
    public function create(string $transactionId, string $action, string $reason, array $items): array
    {
        if ($transactionId === '' || $action === '' || $reason === '' || $items === []) {
            throw new InvalidArgumentException('"transactionId", "action", "reason" and "items" are all required.');
        }

        return $this->client->post('/adjustments', [
            'transaction_id' => $transactionId,
            'action' => $action,
            'reason' => $reason,
            'items' => $items,
        ]);
    }

    /** Signed URL to the credit note PDF for a credit adjustment. */
    public function creditNote(string $id): array
    {
        return $this->client->get('/adjustments/'.$id.'/credit-note');
    }
}
