<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - E-commerce</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: 0.3s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #34495e;
            padding-left: 30px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar-admin {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        .dashboard-card {
            border-radius: 8px;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h5 class="text-white mb-0">
                <i class="fas fa-store"></i> Admin Panel
            </h5>
            <small class="text-muted">E-commerce Dashboard</small>
        </div>
        
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="@if(request()->route()->getName() === 'admin.dashboard') active @endif">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#orders" class="@if(str_contains(request()->route()->getName(), 'orders')) active @endif">
                <i class="fas fa-shopping-bag"></i> Orders
            </a>
            <a href="#products" class="@if(str_contains(request()->route()->getName(), 'products')) active @endif">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="#customers" class="@if(str_contains(request()->route()->getName(), 'customers')) active @endif">
                <i class="fas fa-users"></i> Customers
            </a>
            <a href="#reports" class="@if(str_contains(request()->route()->getName(), 'reports')) active @endif">
                <i class="fas fa-file-chart-line"></i> Reports
            </a>
            <a href="#settings" class="@if(str_contains(request()->route()->getName(), 'settings')) active @endif">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-admin mb-4">
            <div class="container-fluid">
                <span class="navbar-text ms-auto">
                    <span class="me-3">{{ auth()->user()->name }}</span>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="btn btn-sm btn-outline-danger">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </span>
            </div>
        </nav>

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
