<?php

namespace App\Services;

class PaymentService
{
    protected $client;

    public function __construct()
    {
        // Configure a payment client here (e.g. Stripe) when available:
        // $this->client = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    /**
     * Charge the given amount (in cents) and return standardized response
     *
     * @param array $payload ['amount' => int, 'currency' => 'usd', 'source' => 'tok_...']
     * @return array
     */
    public function charge(array $payload): array
    {
        // TODO: Replace with real provider integration
        if (empty($payload['amount']) || $payload['amount'] <= 0) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        return [
            'success' => true,
            'transaction_id' => 'txn_' . uniqid(),
            'amount' => $payload['amount'],
        ];
    }
}
