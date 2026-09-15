<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Sales
        $totalSales = Order::sum('total_amount') / 100;
        
        // Total Orders
        $totalOrders = Order::count();
        
        // Total Products
        $totalProducts = Product::count();
        
        // Total Users
        $totalUsers = User::count();
        
        // Orders this month
        $ordersThisMonth = Order::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();
        
        // Revenue this month
        $revenueThisMonth = Order::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->sum('total_amount') / 100;
        
        // Sales trend (last 30 days)
        $salesTrend = $this->getSalesTrend();
        
        // Top Products
        $topProducts = $this->getTopProducts();
        
        // Recent Orders
        $recentOrders = Order::with('items')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Order Status Distribution
        $orderStatus = Order::groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get();
        
        // Low Stock Products
        $lowStockProducts = Product::where('stock', '<', 20)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', [
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'orderStatus' => $orderStatus,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    private function getSalesTrend()
    {
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $sales = Order::whereDate('created_at', $date)
                ->sum('total_amount') / 100;
            $trend[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'sales' => $sales,
            ];
        }
        return $trend;
    }

    private function getTopProducts()
    {
        return \DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('products.id, products.name, COUNT(order_items.id) as quantity_sold, SUM(order_items.quantity) as total_quantity')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
    }
}
