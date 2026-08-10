@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    @forelse($products as $product)
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          @if($product->image_path)
            <img src="{{ asset($product->image_path) }}" class="card-img-top" alt="{{ $product->name }}">
          @endif
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text">{{ Str::limit($product->description, 120) }}</p>
            <div class="mt-auto">
              <p class="mb-2"><strong>${{ number_format($product->price / 100, 2) }}</strong></p>
              <form action="{{ route('cart.add', $product) }}" method="POST">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn-primary">Add to cart</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @empty
      <p>No products found.</p>
    @endforelse
  </div>
</div>
@endsection
