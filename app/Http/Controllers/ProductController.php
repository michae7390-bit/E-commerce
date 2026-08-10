<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of products.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $query = Product::query()->orderBy('created_at', 'desc');

        // Optional simple search by name or description
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate($perPage)->withQueryString();

        return view('products.index', compact('products'));
    }

    /**
     * Display a single product.
     *
     * @param Product $product
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}
