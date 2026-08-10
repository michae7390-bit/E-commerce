@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h1>Checkout</h1>

      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <div class="card mb-4">
        <div class="card-body">
          <h4>Order summary</h4>

          <ul class="list-group mb-3">
            @foreach($items as $line)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $line['product']->name }}</strong>
                  <div class="small text-muted">Qty: {{ $line['quantity'] }}</div>
                </div>
                <span>${{ number_format($line['subtotal'] / 100, 2) }}</span>
              </li>
            @endforeach

            <li class="list-group-item d-flex justify-content-between">
              <strong>Total</strong>
              <strong>${{ number_format($total / 100, 2) }}</strong>
            </li>
          </ul>

          <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf

            <div class="form-group">
              <label for="name">Full name</label>
              <input id="name" name="name" type="text" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="card-element">Card</label>
              <div id="card-element" style="padding: 12px; border: 1px solid #ced4da; border-radius: .25rem;"></div>
              <div id="card-errors" role="alert" class="text-danger mt-2"></div>
            </div>

            <input type="hidden" name="payment_method" id="payment_method_input">

            <button id="submit-button" class="btn btn-primary">Pay ${{ number_format($total / 100, 2) }}</button>
          </form>
        </div>
      </div>

      <a href="{{ route('cart.index') }}">&larr; Back to cart</a>
    </div>
  </div>
</div>

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const stripeKey = '{{ config('services.stripe.key') ?: env('STRIPE_KEY') }}';
  if (!stripeKey) {
    document.getElementById('card-errors').textContent = 'Stripe publishable key not configured. Set STRIPE_KEY in your .env.';
    document.getElementById('submit-button').disabled = true;
    return;
  }

  const stripe = Stripe(stripeKey);
  const elements = stripe.elements();
  const style = {
    base: {
      color: '#32325d',
      fontFamily: 'Arial, sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '16px',
      '::placeholder': { color: '#aab7c4' }
    },
    invalid: { color: '#fa755a', iconColor: '#fa755a' }
  };

  const card = elements.create('card', { style: style });
  card.mount('#card-element');

  card.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
  });

  const form = document.getElementById('checkout-form');
  const submitButton = document.getElementById('submit-button');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';

    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;

    const {error, paymentMethod} = await stripe.createPaymentMethod({
      type: 'card',
      card: card,
      billing_details: { name: name, email: email }
    });

    if (error) {
      document.getElementById('card-errors').textContent = error.message;
      submitButton.disabled = false;
      submitButton.textContent = 'Pay ${{ number_format($total / 100, 2) }}';
      return;
    }

    // Insert the payment method id into the form and submit
    document.getElementById('payment_method_input').value = paymentMethod.id;
    form.submit();
  });
});
</script>
@endsection

@endsection