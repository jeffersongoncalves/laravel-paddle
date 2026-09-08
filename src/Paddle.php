<?php

namespace JeffersonGoncalves\Paddle;

use JeffersonGoncalves\Paddle\Resources\Adjustments;
use JeffersonGoncalves\Paddle\Resources\Customers;
use JeffersonGoncalves\Paddle\Resources\Discounts;
use JeffersonGoncalves\Paddle\Resources\Events;
use JeffersonGoncalves\Paddle\Resources\Notifications;
use JeffersonGoncalves\Paddle\Resources\Prices;
use JeffersonGoncalves\Paddle\Resources\Products;
use JeffersonGoncalves\Paddle\Resources\Subscriptions;
use JeffersonGoncalves\Paddle\Resources\Transactions;

/**
 * Entry point exposing one resource per Paddle Billing API v1 group, plus
 * webhook signature verification.
 */
class Paddle
{
    protected PaddleClient $client;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.paddle.com',
        protected int $defaultPerPage = 50,
        protected ?string $webhookSecret = null,
    ) {
        $this->client = new PaddleClient($apiKey, $baseUrl);
    }

    public function products(): Products
    {
        return new Products($this->client, $this->defaultPerPage);
    }

    public function prices(): Prices
    {
        return new Prices($this->client, $this->defaultPerPage);
    }

    public function customers(): Customers
    {
        return new Customers($this->client, $this->defaultPerPage);
    }

    public function subscriptions(): Subscriptions
    {
        return new Subscriptions($this->client, $this->defaultPerPage);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this->client, $this->defaultPerPage);
    }

    public function discounts(): Discounts
    {
        return new Discounts($this->client, $this->defaultPerPage);
    }

    public function adjustments(): Adjustments
    {
        return new Adjustments($this->client, $this->defaultPerPage);
    }

    public function events(): Events
    {
        return new Events($this->client, $this->defaultPerPage);
    }

    public function notifications(): Notifications
    {
        return new Notifications($this->client, $this->defaultPerPage);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->webhookSecret);
    }
}
