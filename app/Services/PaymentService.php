<?php

namespace App\Services;

class PaymentService
{
    protected $client;

    public function __construct()
    {
        // If Stripe is configured, instantiate its client
        if (config('services.stripe.secret')) {
            $this->client = new \Stripe\StripeClient(config('services.stripe.secret'));
        }
    }

    /**
     * Charge the given amount (in cents) and return standardized response
     *
     * @param array $payload ['amount' => int, 'currency' => 'usd', 'source' => 'tok_...']
     * @return array
     */
    public function charge(array $payload): array
    {
        if (empty($payload['amount']) || $payload['amount'] <= 0) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        // If Stripe client is available, create a PaymentIntent or Charge depending on source
        if ($this->client) {
            try {
                // Prefer PaymentIntents API if a payment_method is provided
                if (str_starts_with($payload['source'], 'pm_') || str_starts_with($payload['source'], 'pm-')) {
                    $pi = $this->client->paymentIntents->create([
                        'amount' => $payload['amount'],
                        'currency' => $payload['currency'] ?? 'usd',
                        'payment_method' => $payload['source'],
                        'confirmation_method' => 'automatic',
                        'confirm' => true,
                        'metadata' => $payload['metadata'] ?? [],
                    ]);

                    return ['success' => true, 'transaction_id' => $pi->id, 'raw' => $pi];
                }

                // Fallback to Charges API for tokens (tok_)
                $charge = $this->client->charges->create([
                    'amount' => $payload['amount'],
                    'currency' => $payload['currency'] ?? 'usd',
                    'source' => $payload['source'],
                    'metadata' => $payload['metadata'] ?? [],
                ]);

                return ['success' => true, 'transaction_id' => $charge->id, 'raw' => $charge];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return ['success' => false, 'message' => $e->getMessage(), 'exception' => $e];
            }
        }

        // Local fallback: simulate success
        return [
            'success' => true,
            'transaction_id' => 'txn_' . uniqid(),
            'amount' => $payload['amount'],
        ];
    }
}
