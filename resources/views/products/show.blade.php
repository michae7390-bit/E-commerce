@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-4">
        @if($product->image_path)
          <img src="{{ asset($product->image_path) }}" class="card-img-top" alt="{{ $product->name }}">
        @endif
        <div class="card-body">
          <h1 class="card-title">{{ $product->name }}</h1>
          <p class="text-muted">SKU: {{ $product->id }} • Stock: {{ $product->stock }}</p>
          <h3 class="text-primary">${{ number_format($product->price / 100, 2) }}</h3>
          <p class="mt-3">{{ $product->description }}</p>

          <div class="mt-4">
            @if($product->stock > 0)
              <form action="{{ route('cart.add', $product) }}" method="POST" class="form-inline">
                @csrf
                <div class="form-group mr-2">
                  <label for="quantity" class="sr-only">Quantity</label>
                  <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="form-control" style="width:90px;">
                </div>
                <button class="btn btn-success">Add to cart</button>
              </form>
            @else
              <div class="alert alert-warning">Out of stock</div>
            @endif
          </div>
        </div>
      </div>

      <a href="{{ route('products.index') }}">&larr; Back to products</a>
    </div>
  </div>
</div>
@endsection
