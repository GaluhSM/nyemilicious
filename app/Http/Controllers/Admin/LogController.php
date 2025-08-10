<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::latest()->paginate(20);
        
        $stats = [
            'total_products' => Product::count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total'),
            'total_accounts' => User::count(),
        ];

        return view('admin.logs.index', compact('logs', 'stats'));
    }
}
