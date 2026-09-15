<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the customer dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $totalOrders = Order::where('user_id', $user->id)->count();
        
        $totalSpent = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        return view('dashboard.index', compact('user', 'orders', 'totalOrders', 'totalSpent'));
    }

    /**
     * Display a specific order details
     */
    public function showOrder($orderId)
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->with('items')
            ->firstOrFail();

        return view('dashboard.order-details', compact('order'));
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('dashboard.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Display order history
     */
    public function orderHistory()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('dashboard.order-history', compact('orders'));
    }
}
