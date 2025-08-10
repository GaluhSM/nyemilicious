<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'delivery_type' => 'required|in:pickup,delivery',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderCode = $this->generateOrderCode();
        $subtotal = 0;
        $shippingFee = $request->delivery_type === 'delivery' ? 2000 : 0;

        // Calculate subtotal
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $total = $subtotal + $shippingFee;

        $order = Order::create([
            'order_code' => $orderCode,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'delivery_type' => $request->delivery_type,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => $total,
        ]);

        // Create order items
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

        // Generate QR Code
        if ($request->delivery_type === 'pickup') {
            $qrContent = route('order.status', $orderCode);
            $qrPath = 'qr-codes/' . $orderCode . '.svg';
            QrCode::size(200)->generate($qrContent, public_path('storage/' . $qrPath));
            $order->update(['qr_code_path' => $qrPath]);
        }

        // Log activity
        ActivityLog::log('sistem', 'Membuat pesanan baru: ' . $orderCode, 'Order', $order->toArray());

        return redirect()->route('order.status', $orderCode);
    }

    public function show($orderCode)
    {
        $order = Order::with('orderItems.product')->where('order_code', $orderCode)->firstOrFail();
        return view('order-status', compact('order'));
    }

    public function cancel($orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();
        $order->update(['payment_status' => 'cancel']);
        
        ActivityLog::log('sistem', 'Membatalkan pesanan: ' . $orderCode, 'Order');
        
        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan');
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
