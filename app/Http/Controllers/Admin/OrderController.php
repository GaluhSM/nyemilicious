<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('orderItems.product');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->today();
                    break;
                case 'week':
                    $query->thisWeek();
                    break;
                case 'month':
                    $query->thisMonth();
                    break;
            }
        }

        // Product filter
        if ($request->filled('product_filter')) {
            $query->whereHas('orderItems', function ($q) use ($request) {
                $q->where('product_id', $request->product_filter);
            });
        }

        $orders = $query->latest()->paginate(15);
        $products = Product::all();

        $stats = [
            'total_products' => Product::count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total'),
            'total_accounts' => User::count(),
        ];

        return view('admin.orders.index', compact('orders', 'products', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderCode = $this->generateOrderCode();
        $subtotal = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'order_code' => $orderCode,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone ?? '',
            'customer_address' => $request->customer_address,
            'delivery_type' => 'pickup',
            'subtotal' => $subtotal,
            'shipping_fee' => 0,
            'total' => $subtotal,
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $itemTotal,
            ]);
        }

        ActivityLog::log(Auth::user()->username, 'Membuat pesanan baru: ' . $orderCode, 'Order');

        return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil dibuat');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'payment_status' => 'required|in:pending,paid,cancel',
            'order_status' => 'required|in:pending,confirmed,in_progress,ready,shipped,completed',
        ]);

        $order->update($request->only([
            'customer_name',
            'customer_phone',
            'customer_address',
            'payment_status',
            'order_status'
        ]));

        ActivityLog::log(Auth::user()->username, 'Mengupdate pesanan: ' . $order->order_code, 'Order');

        return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil diupdate');
    }

    public function destroy(Order $order)
    {
        $orderCode = $order->order_code;
        $order->delete();

        ActivityLog::log(Auth::user()->username, 'Menghapus pesanan: ' . $orderCode, 'Order');

        return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil dihapus');
    }

    private function generateOrderCode()
    {
        $prefix = 'NYEMIL';
        $random8 = Str::random(8);
        $date = now()->format('d');
        $random3Letters = Str::random(3);
        $month = now()->format('m');
        $random2Numbers = rand(10, 99);
        $year = now()->format('Y');

        return strtoupper($prefix . '-' . $random8 . '-' . $date . $random3Letters . $month . $random2Numbers . $year);
    }
}
