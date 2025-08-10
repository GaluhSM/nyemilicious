<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Debug log - hapus setelah debug selesai
            Log::info('Order request data:', $request->all());

            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_address' => 'required|string',
                'delivery_type' => 'required|in:pickup,delivery',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $orderCode = $this->generateOrderCode();
            $subtotal = 0;
            $shippingFee = $request->delivery_type === 'delivery' ? 2000 : 0;

            // Calculate subtotal dengan error handling
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }
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

            // Create order items dengan error handling
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

            // Generate QR Code hanya jika pickup
            if ($request->delivery_type === 'pickup') {
                try {
                    $this->generateQRCode($order, $orderCode);
                } catch (Exception $e) {
                    // Log error tapi jangan gagalkan order
                    Log::warning('QR Code generation failed: ' . $e->getMessage());
                }
            }

            // Log activity
            ActivityLog::log('sistem', 'Membuat pesanan baru: ' . $orderCode, 'Order', $order->toArray());

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'order_code' => $orderCode,
                'redirect_url' => route('order.status', $orderCode)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateQRCode($order, $orderCode)
    {
        // Cek apakah QR Code package tersedia
        if (!class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            throw new Exception('QR Code package not available');
        }

        $qrContent = route('order.status', $orderCode);
        $qrPath = 'qr-codes/' . $orderCode . '.svg';
        
        // Create directory if not exists
        $qrDirectory = public_path('storage/qr-codes');
        if (!file_exists($qrDirectory)) {
            if (!mkdir($qrDirectory, 0755, true)) {
                throw new Exception('Cannot create QR code directory');
            }
        }
        
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)
            ->generate($qrContent, public_path('storage/' . $qrPath));
        
        $order->update(['qr_code_path' => $qrPath]);
    }

    // Method lainnya tetap sama...
    public function show($orderCode)
    {
        $order = Order::with('orderItems.product')->where('order_code', $orderCode)->firstOrFail();
        return view('order-status', compact('order'));
    }

    public function cancel($orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();
        
        if ($order->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena status pembayaran bukan pending'
            ]);
        }
        
        $order->update(['payment_status' => 'cancel']);
        ActivityLog::log('sistem', 'Membatalkan pesanan: ' . $orderCode, 'Order');
        
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan'
        ]);
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