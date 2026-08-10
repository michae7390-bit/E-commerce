<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Services\PaymentService;

class ProcessStripeCharge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    public $timeout = 120;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(PaymentService $payments)
    {
        $order = Order::find($this->orderId);
        if (! $order) {
            return;
        }

        // read payment method from order meta
        $paymentMethod = data_get($order->meta, 'payment_method');
        $amount = (int) $order->total_amount;

        if (! $paymentMethod || $amount <= 0) {
            $order->update(['status' => 'failed', 'meta' => array_merge((array)$order->meta, ['failure' => 'Invalid payment data'])]);
            return;
        }

        $payload = [
            'amount' => $amount,
            'currency' => config('app.currency', 'usd'),
            'source' => $paymentMethod,
            'metadata' => ['order_id' => $order->id],
        ];

        $result = $payments->charge($payload);

        if (! empty($result['success'])) {
            $order->update(['status' => 'paid', 'meta' => array_merge((array)$order->meta, ['transaction_id' => $result['transaction_id'] ?? null])]);

            // TODO: dispatch notifications, send receipt email, update inventory, etc.
        } else {
            $order->update(['status' => 'failed', 'meta' => array_merge((array)$order->meta, ['failure' => $result['message'] ?? 'Payment failed'])]);

            // Optionally retry or notify admin
        }
    }
}
