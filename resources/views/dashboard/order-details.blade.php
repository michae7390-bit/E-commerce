@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('dashboard.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Back to Dashboard</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                    <p class="text-gray-600 text-sm mt-1">{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                </div>

                <div class="px-6 py-6">
                    <!-- Order Items -->
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Order Items</h2>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->items as $item)
                                    <tr class="border-b border-gray-200">
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                            {{ $item->product->name ?? 'Product Removed' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ $item->quantity }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No items in order</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-end mb-4">
                            <div class="w-80">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Shipping</span>
                                    <span class="font-medium text-gray-900">FREE</span>
                                </div>
                                <div class="flex justify-between mb-4 pb-4 border-b border-gray-200">
                                    <span class="text-gray-600">Tax</span>
                                    <span class="font-medium text-gray-900">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lg font-bold text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div>
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow mb-6 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Order Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if ($order->status === 'completed')
                                <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @elseif ($order->status === 'pending')
                                <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.5a1 1 0 002 0V7z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                @if ($order->status === 'completed')
                                    Delivered
                                @elseif ($order->status === 'pending')
                                    Processing
                                @else
                                    {{ ucfirst($order->status) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Shipping To</h3>
                <div class="space-y-2">
                    <p class="text-gray-900 font-medium">{{ $order->meta['customer']['name'] ?? 'N/A' }}</p>
                    <p class="text-gray-600 text-sm">{{ $order->meta['customer']['email'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
