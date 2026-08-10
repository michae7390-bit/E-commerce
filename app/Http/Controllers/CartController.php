<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);

        // Enrich cart items with product details
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $id => $meta) {
            $product = $products->get($id);
            if (! $product) continue;

            $quantity = $meta['quantity'] ?? 0;
            $line = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ];

            $total += $line['subtotal'];
            $items[] = $line;
        }

        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    public function add(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $cart = Session::get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $qty;
        } else {
            $cart[$product->id] = ['quantity' => $qty];
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $qty = max(0, (int) $request->input('quantity', 1));
        $cart = Session::get('cart', []);

        if ($qty === 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id]['quantity'] = $qty;
        }

        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(Product $product)
    {
        $cart = Session::get('cart', []);
        unset($cart[$product->id]);
        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }
}
