<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total'),
            'total_accounts' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
