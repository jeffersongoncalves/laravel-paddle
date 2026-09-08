<?php

namespace JeffersonGoncalves\Paddle;

use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Verification of Paddle's "Paddle-Signature" webhook header.
 *
 * The header looks like "ts=1671552777;h1=eb4d0dc...", and the signed payload
 * is "<ts>:<raw request body>" hashed with HMAC-SHA256 using the notification
 * destination's secret key.
 */
class Webhooks
{
    public function __construct(
        protected ?string $secret = null,
    ) {}

    /**
     * @param  string  $payload  The RAW request body — a re-encoded array will not match.
     * @param  int  $maxAge  Seconds of clock skew tolerated; 0 disables the check.
     */
    public function verify(string $payload, string $signature, int $maxAge = 5): bool
    {
        if ($this->secret === null || $this->secret === '') {
            throw new InvalidArgumentException('No Paddle webhook secret configured. Set PADDLE_WEBHOOK_SECRET.');
        }

        $parts = [];

        foreach (explode(';', $signature) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

            if ($value !== null) {
                $parts[trim($key)] = $value;
            }
        }

        $timestamp = $parts['ts'] ?? null;
        $hash = $parts['h1'] ?? null;

        if ($timestamp === null || $hash === null) {
            return false;
        }

        if ($maxAge > 0 && abs(time() - (int) $timestamp) > $maxAge) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp.':'.$payload, $this->secret), $hash);
    }

    public function verifyRequest(Request $request, int $maxAge = 5): bool
    {
        return $this->verify(
            $request->getContent(),
            (string) $request->header('Paddle-Signature'),
            $maxAge,
        );
    }
}
