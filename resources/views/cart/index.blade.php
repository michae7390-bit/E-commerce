@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Your Cart</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(count($items) === 0)
    <p>Your cart is empty.</p>
  @else
    <table class="table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $line)
          <tr>
            <td>{{ $line['product']->name }}</td>
            <td>
              <form action="{{ route('cart.update', $line['product']) }}" method="POST" class="d-inline">
                @csrf
                <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" style="width:80px;" class="form-control d-inline-block">
                <button class="btn btn-sm btn-secondary">Update</button>
              </form>
            </td>
            <td>${{ number_format($line['product']->price / 100, 2) }}</td>
            <td>${{ number_format($line['subtotal'] / 100, 2) }}</td>
            <td>
              <form action="{{ route('cart.remove', $line['product']) }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-danger">Remove</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="text-right">
      <h4>Total: ${{ number_format($total / 100, 2) }}</h4>
    </div>
  @endif
</div>
@endsection
