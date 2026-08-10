<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhooks. Requires STRIPE_WEBHOOK_SECRET in .env to verify signatures.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if ($webhookSecret) {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (\UnexpectedValueException $e) {
                // Invalid payload
                Log::warning('Stripe webhook invalid payload: ' . $e->getMessage());
                return response('Invalid payload', 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
                return response('Invalid signature', 400);
            }
        } else {
            // No verification configured — attempt best-effort parse
            $event = json_decode($payload);
        }

        // Handle a few relevant events
        $type = data_get($event, 'type');

        if ($type === 'payment_intent.succeeded' || $type === 'charge.succeeded') {
            $metadata = data_get($event, 'data.object.metadata', []);
            $orderId = $metadata->order_id ?? ($metadata['order_id'] ?? null);
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['status' => 'paid', 'meta' => array_merge((array)$order->meta, ['webhook' => $event])]);
                }
            }
        }

        // Respond 200 to acknowledge receipt
        return response('Received', 200);
    }
}
