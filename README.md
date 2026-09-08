<div class="filament-hidden">

![Laravel Paddle](https://raw.githubusercontent.com/jeffersongoncalves/laravel-paddle/main/art/jeffersongoncalves-laravel-paddle.png)

</div>

# Laravel Paddle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-paddle.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-paddle)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-paddle/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-paddle/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-paddle/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-paddle/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-paddle.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-paddle)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-paddle.svg?style=flat-square)](LICENSE.md)

A PHP/Laravel client for the [Paddle](https://www.paddle.com/) Billing API v1. Covers products, prices, customers, subscriptions, transactions, discounts, adjustments, events and notifications through a simple, typed API built on Laravel's `Http` client — plus verification of Paddle's webhook signatures.

## Features

- Products: list, get, create, update, archive
- Prices: list, get, create, update, archive
- Customers: list, get, create, update, archive, credit balances
- Subscriptions: list, get, update, cancel, pause, resume, activate, one-off charge, payment-method transaction
- Transactions: list, get, create, update, invoice URL
- Discounts: list, get, create, update, archive
- Adjustments: list, create (refund/credit/chargeback), credit-note URL
- Events: event stream and event-type catalog
- Notifications: list, get, replay, delivery logs
- Webhooks: `Paddle-Signature` verification (HMAC-SHA256, replay-window check)
- Sandbox support via a single env flag
- Throws `PaddleException` (with the original API error body and Paddle error code) on any non-2xx response
- Throws `InvalidArgumentException` before hitting the API when a required field is missing

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-paddle
```

Publish the config file:

```bash
php artisan vendor:publish --tag=paddle-config
```

Set your credentials in `.env`:

```env
PADDLE_API_KEY=your-api-key
PADDLE_SANDBOX=false
PADDLE_WEBHOOK_SECRET=your-notification-secret
```

Create the API key under **Developer Tools > Authentication** in Paddle. Sandbox and live keys are separate — a sandbox key only works with `PADDLE_SANDBOX=true`.

## Configuration

```php
// config/paddle.php
return [
    'api_key' => env('PADDLE_API_KEY'),
    'sandbox' => (bool) env('PADDLE_SANDBOX', false),
    'base_url' => env('PADDLE_BASE_URL', env('PADDLE_SANDBOX', false)
        ? 'https://sandbox-api.paddle.com'
        : 'https://api.paddle.com'),
    'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
    'default_per_page' => env('PADDLE_DEFAULT_PER_PAGE', 50),
];
```

## Usage

Use the `Paddle` facade or inject `JeffersonGoncalves\Paddle\Paddle`. Each API group is exposed as a method returning a dedicated resource class. Every `list()` accepts Paddle's own filters (`status`, `after`, `per_page`, `order_by`, `id`, ...) and defaults `per_page` to `default_per_page`.

### Products and prices

```php
use JeffersonGoncalves\Paddle\Facades\Paddle;

$products = Paddle::products()->list(['status' => 'active']);

$product = Paddle::products()->create([
    'name' => 'Pro Plan',
    'tax_category' => 'saas',
    'description' => 'Everything in one plan',
]);

Paddle::products()->update($product['data']['id'], ['name' => 'Pro Plan (annual)']);
Paddle::products()->archive($product['data']['id']);

$price = Paddle::prices()->create(
    productId: $product['data']['id'],
    amount: 1990,            // lowest denomination — 1990 = US$ 19.90
    currencyCode: 'USD',
    description: 'Monthly',
    attributes: ['billing_cycle' => ['interval' => 'month', 'frequency' => 1]],
);
```

### Customers

```php
$customer = Paddle::customers()->create('jane@example.com', ['name' => 'Jane Doe']);

Paddle::customers()->update($customer['data']['id'], ['name' => 'Janet Doe']);
Paddle::customers()->creditBalances($customer['data']['id']);
Paddle::customers()->archive($customer['data']['id']);
```

### Subscriptions

```php
$subscriptions = Paddle::subscriptions()->list(['customer_id' => 'ctm_123']);

Paddle::subscriptions()->update('sub_123', [
    'proration_billing_mode' => 'prorated_immediately',
    'items' => [['price_id' => 'pri_123', 'quantity' => 3]],
]);

Paddle::subscriptions()->cancel('sub_123');                         // at period end
Paddle::subscriptions()->cancel('sub_123', 'immediately');
Paddle::subscriptions()->pause('sub_123', '2026-12-01T00:00:00Z');  // omit to pause indefinitely
Paddle::subscriptions()->resume('sub_123');
Paddle::subscriptions()->activate('sub_123');                       // trialing -> active

// One-off charge on top of the subscription
Paddle::subscriptions()->charge('sub_123', [['price_id' => 'pri_123', 'quantity' => 1]], 'immediately');

// Transaction that lets the customer update their card
Paddle::subscriptions()->updatePaymentMethodTransaction('sub_123');
```

### Transactions, discounts and adjustments

```php
$transaction = Paddle::transactions()->create(
    items: [['price_id' => 'pri_123', 'quantity' => 1]],
    attributes: ['customer_id' => 'ctm_123', 'collection_mode' => 'manual'],
);

Paddle::transactions()->invoice($transaction['data']['id']);

Paddle::discounts()->create(25, 'percentage', 'Launch', ['code' => 'LAUNCH25']);

// Refund a transaction item
Paddle::adjustments()->create(
    transactionId: $transaction['data']['id'],
    action: 'refund',
    reason: 'Customer request',
    items: [['item_id' => 'txnitm_123', 'type' => 'full']],
);
```

### Events and notifications

```php
Paddle::events()->list(['after' => 'evt_123']);
Paddle::events()->types();

Paddle::notifications()->list(['status' => 'failed']);
Paddle::notifications()->logs('ntf_123');
Paddle::notifications()->replay('ntf_123');
```

### Webhooks

Verify the `Paddle-Signature` header against the raw request body before trusting a webhook:

```php
use Illuminate\Http\Request;
use JeffersonGoncalves\Paddle\Facades\Paddle;

Route::post('/paddle/webhook', function (Request $request) {
    abort_unless(Paddle::webhooks()->verifyRequest($request), 403);

    $event = $request->json()->all();

    // handle $event['event_type'] ...

    return response()->noContent();
})->withoutMiddleware([VerifyCsrfToken::class]);
```

`verify(string $payload, string $signature, int $maxAge = 5)` is available when you already hold the raw body. Signatures older than `$maxAge` seconds are rejected (pass `0` to disable that check). The payload **must** be the raw body — a re-encoded array will not match.

### Error handling

Any non-2xx API response throws `JeffersonGoncalves\Paddle\Exceptions\PaddleException`, which exposes both the decoded error body and Paddle's machine-readable code:

```php
use JeffersonGoncalves\Paddle\Exceptions\PaddleException;

try {
    Paddle::subscriptions()->get('sub_missing');
} catch (PaddleException $e) {
    if ($e->errorCode() === 'entity_not_found') {
        // ...
    }

    logger()->error($e->getMessage(), $e->errorBody());
}
```

Missing required fields (e.g. `name`/`tax_category` on `products()->create()`) throw `InvalidArgumentException` before any HTTP call is made.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
