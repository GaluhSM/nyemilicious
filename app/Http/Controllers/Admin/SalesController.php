<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        $query = Order::paid()->completed();
        
        switch ($filter) {
            case 'today':
                $query = $query->today();
                break;
            case 'week':
                $query = $query->thisWeek();
                break;
            case 'month':
                $query = $query->thisMonth();
                break;
        }

        $completedOrders = $query->with('orderItems.product')->get();
        $totalRevenue = $completedOrders->sum('total');

        // Product sales data for chart
        $productSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.order_status', 'completed')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->get();

        $totalSold = $productSales->sum('total_sold');
        $chartData = $productSales->map(function ($item) use ($totalSold) {
            return [
                'name' => $item->name,
                'percentage' => $totalSold > 0 ? round(($item->total_sold / $totalSold) * 100, 1) : 0
            ];
        });

        $stats = [
            'total_products' => Product::count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total'),
            'total_accounts' => User::count(),
        ];

        return view('admin.sales', compact('completedOrders', 'totalRevenue', 'chartData', 'filter', 'stats'));
    }
}
