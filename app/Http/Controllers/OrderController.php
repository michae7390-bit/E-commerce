<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Jobs\ProcessStripeCharge;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty.');
        }

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $id => $meta) {
            $product = $products->get($id);
            if (! $product) continue;
            $quantity = $meta['quantity'] ?? 0;
            $subtotal = $product->price * $quantity;
            $items[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            $total += $subtotal;
        }

        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            // Expect a Stripe payment method id or token from frontend (e.g. pm_..., tok_...)
            'payment_method' => 'required|string',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty.');
        }

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Build order data and calculate total
        $total = 0;
        $itemsPayload = [];

        foreach ($cart as $id => $meta) {
            $product = $products->get($id);
            if (! $product) continue;
            $quantity = max(0, (int) ($meta['quantity'] ?? 0));
            if ($quantity === 0) continue;

            $unitPrice = $product->price;
            $subtotal = $unitPrice * $quantity;
            $total += $subtotal;

            $itemsPayload[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        if (count($itemsPayload) === 0) {
            return redirect()->route('cart.index')->with('error', 'No valid items in cart.');
        }

        // Persist order and items atomically
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id() ?: null,
                'total_amount' => $total,
                'status' => 'pending',
                'meta' => [
                    'customer' => [
                        'name' => $data['name'],
                        'email' => $data['email'],
                    ],
                    'payment_method' => $data['payment_method'],
                ],
            ]);

            foreach ($itemsPayload as $it) {
                OrderItem::create(array_merge($it, ['order_id' => $order->id]));
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', 'Could not create order: ' . $ex->getMessage());
        }

        // Dispatch payment job to queue
        ProcessStripeCharge::dispatch($order->id);

        // Clear cart (we keep order in DB while payment is pending)
        Session::forget('cart');

        return redirect()->route('products.index')->with('success', 'Order placed. Payment is being processed. You will receive confirmation via email.');
    }
}
