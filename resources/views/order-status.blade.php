<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - NYEMIL</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <!-- Header -->
    <header>
        <div>
            <h1>Status Pesanan</h1>
            <p>Kode Pesanan: <strong>{{ $order->order_code }}</strong></p>
        </div>
    </header>

    <!-- QR Code Section -->
    <section>
        <div>
            <h3>QR Code Pembayaran</h3>
            @if($order->delivery_type === 'delivery')
                <img src="{{ asset('images/qris.png') }}" alt="QRIS Toko" style="max-width: 200px; height: auto;">
                <p>Scan QR Code di atas untuk pembayaran via QRIS</p>
            @else
                @if($order->qr_code_path)
                    <img src="{{ asset('storage/' . $order->qr_code_path) }}" alt="QR Code Pesanan" style="max-width: 200px; height: auto;">
                @endif
                <p>Tunjukkan QR Code ini saat pengambilan di bazar</p>
            @endif
        </div>
    </section>

    <!-- Order Summary -->
    <section>
        <h3>Ringkasan Pesanan</h3>
        <div>
            @foreach($order->orderItems as $item)
            <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span>{{ $item->product->name }} (x{{ $item->quantity }})</span>
                    <span>Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            
            <div style="padding: 10px 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            
            @if($order->shipping_fee > 0)
            <div style="padding: 10px 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Ongkos Kirim</span>
                    <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                </div>
            </div>
            @endif
            
            <div style="padding: 10px 0; font-weight: bold; border-top: 1px solid #ddd;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Summary -->
    <section>
        <h3>Data Pelanggan</h3>
        <div>
            <p><strong>Nama:</strong> {{ $order->customer_name }}</p>
            <p><strong>Telepon:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Alamat:</strong> {{ $order->customer_address ?? '-' }}</p>
            <p><strong>Jenis Pengiriman:</strong> 
                {{ $order->delivery_type === 'pickup' ? 'Ambil di Bazar' : 'Antar ke Rumah' }}
            </p>
        </div>
    </section>

    <!-- Payment Status -->
    <section>
        <h3>Status Pembayaran</h3>
        <div>
            <span class="status-badge status-{{ $order->payment_status }}">
                @switch($order->payment_status)
                    @case('pending') Menunggu Pembayaran @break
                    @case('paid') Sudah Dibayar @break
                    @case('cancel') Dibatalkan @break
                @endswitch
            </span>
        </div>
    </section>

    <!-- Order Status -->
    <section>
        <h3>Status Pesanan</h3>
        <div id="orderStatusSteps">
            <div class="status-step {{ $order->order_status === 'pending' ? 'active' : ($order->order_status !== 'pending' ? 'completed' : '') }}">
                <div class="step-number">1</div>
                <div class="step-label">Pending</div>
            </div>
            
            <div class="status-step {{ $order->order_status === 'confirmed' ? 'active' : (in_array($order->order_status, ['in_progress', 'ready', 'shipped', 'completed']) ? 'completed' : '') }}">
                <div class="step-number">2</div>
                <div class="step-label">Dikonfirmasi</div>
            </div>
            
            <div class="status-step {{ $order->order_status === 'in_progress' ? 'active' : (in_array($order->order_status, ['ready', 'shipped', 'completed']) ? 'completed' : '') }}">
                <div class="step-number">3</div>
                <div class="step-label">Sedang Dibuat</div>
            </div>
            
            <div class="status-step {{ $order->order_status === 'ready' ? 'active' : (in_array($order->order_status, ['shipped', 'completed']) ? 'completed' : '') }}">
                <div class="step-number">4</div>
                <div class="step-label">Siap</div>
            </div>
            
            @if($order->delivery_type === 'delivery')
            <div class="status-step {{ $order->order_status === 'shipped' ? 'active' : ($order->order_status === 'completed' ? 'completed' : '') }}">
                <div class="step-number">5</div>
                <div class="step-label">Dikirim</div>
            </div>
            @endif
            
            <div class="status-step {{ $order->order_status === 'completed' ? 'active' : '' }}">
                <div class="step-number">{{ $order->delivery_type === 'delivery' ? '6' : '5' }}</div>
                <div class="step-label">Selesai</div>
            </div>
        </div>
    </section>

    <!-- Action Buttons -->
    <section>
        @if($order->payment_status !== 'cancel' && $order->payment_status !== 'paid')
        <button onclick="cancelOrder()" style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; margin-bottom: 10px;">
            Batalkan Pesanan
        </button>
        @endif
        
        <button onclick="window.location.href='{{ route('landing') }}'" style="background-color: #28a745; color: white; padding: 10px 20px; border: none;">
            Kembali ke Beranda
        </button>
    </section>

    <!-- Warning Modal for Leaving Page -->
    <div id="warningModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 400px;">
            <h3>Peringatan!</h3>
            <p>Harap simpan URL halaman ini agar Anda dapat melihat status pesanan kembali nanti.</p>
            <p><strong>URL:</strong> <span id="currentUrl"></span></p>
            <div style="margin-top: 20px;">
                <button onclick="copyUrl()" style="background-color: #007bff; color: white; padding: 10px 15px; border: none; margin-right: 10px;">
                    Salin URL
                </button>
                <button onclick="closeModal()" style="background-color: #6c757d; color: white; padding: 10px 15px; border: none;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-paid { background-color: #28a745; color: white; }
        .status-cancel { background-color: #dc3545; color: white; }

        #orderStatusSteps {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .status-step {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #e9ecef;
            color: #6c757d;
        }
        
        .status-step.active .step-number {
            background-color: #007bff;
            color: white;
        }
        
        .status-step.completed .step-number {
            background-color: #28a745;
            color: white;
        }
        
        .step-label {
            font-weight: 500;
        }
    </style>

    <script>
        // Set base URL for JavaScript
        const baseUrl = '{{ url('/') }}';
        
        function cancelOrder() {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                fetch('{{ route('order.cancel', $order->order_code) }}', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan');
                        location.reload();
                    } else {
                        alert('Gagal membatalkan pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membatalkan pesanan');
                });
            }
        }

        function showWarningModal() {
            document.getElementById('currentUrl').textContent = window.location.href;
            document.getElementById('warningModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('warningModal').style.display = 'none';
        }

        function copyUrl() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('URL berhasil disalin ke clipboard!');
                closeModal();
            }, function(err) {
                console.error('Gagal menyalin URL: ', err);
                alert('Gagal menyalin URL. Silakan salin manual: ' + url);
            });
        }

        // Show warning when user tries to leave the page
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            showWarningModal();
            return 'Harap simpan URL halaman ini untuk melihat status pesanan nanti.';
        });

        // Auto refresh status every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>