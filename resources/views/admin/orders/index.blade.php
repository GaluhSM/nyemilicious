<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - NYEMIL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #f8f9fa; border-right: 1px solid #ddd; }
        .sidebar h2 { padding: 20px; margin: 0; border-bottom: 1px solid #ddd; }
        .sidebar nav { padding: 0; }
        .sidebar nav a { display: block; padding: 15px 20px; text-decoration: none; color: #333; border-bottom: 1px solid #eee; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #e9ecef; }
        .content { flex: 1; padding: 20px; }
        .stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 10px; }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
        .filter-input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .filter-btn { padding: 8px 15px; border: 1px solid #ddd; background: white; cursor: pointer; text-decoration: none; color: #333; border-radius: 4px; }
        .filter-btn:hover { background: #f8f9fa; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: bold; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:hover { opacity: 0.9; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #ffc107; color: #212529; }
        .status-paid { background: #28a745; color: white; }
        .status-cancel { background: #dc3545; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .order-items { margin-bottom: 15px; }
        .order-item { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
        .logout-btn { display: block; margin: 20px; padding: 10px; background: #dc3545; color: white; text-align: center; text-decoration: none; border-radius: 4px; }
        .logout-btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>NYEMIL Admin</h2>
            <nav>
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.sales') }}">Ringkasan Penjualan</a>
                <a href="{{ route('admin.orders.index') }}" class="active">Pesanan</a>
                <a href="{{ route('admin.accounts.index') }}">Akun</a>
                <a href="{{ route('admin.logs') }}">Log Aktivitas</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin: 20px;">
                @csrf
                <button type="submit" class="logout-btn" style="width: 100%; border: none; cursor: pointer;">Logout</button>
            </form>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Kelola Pesanan</h1>

            @if(session('success'))
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['total_products'] }}</div>
                    <div class="stat-label">Total Produk</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['today_orders'] }}</div>
                    <div class="stat-label">Pesanan Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</div>
                    <div class="stat-label">Pendapatan Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['total_accounts'] }}</div>
                    <div class="stat-label">Total Akun</div>
                </div>
            </div>

            <!-- Create Order Button -->
            <button onclick="openCreateModal()" class="btn btn-success" style="margin-bottom: 20px;">+ Buat Pesanan Baru</button>

            <!-- Filters -->
            <form method="GET" class="filters">
                <input type="text" name="search" placeholder="Cari nama/alamat/telepon..." value="{{ request('search') }}" class="filter-input">
                
                <select name="date_filter" class="filter-input">
                    <option value="">Semua Tanggal</option>
                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                </select>

                <select name="product_filter" class="filter-input">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_filter') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="filter-btn">Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="filter-btn">Reset</a>
            </form>

            <!-- Orders Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Telepon</th>
                        <th>Total</th>
                        <th>Status Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->customer_phone }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="status-badge status-{{ $order->payment_status }}">
                                @switch($order->payment_status)
                                    @case('pending') Pending @break
                                    @case('paid') Dibayar @break
                                    @case('cancel') Dibatalkan @break
                                @endswitch
                            </span>
                        </td>
                        <td>{{ ucfirst($order->order_status) }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button onclick="openUpdateModal({{ $order->id }})" class="btn btn-warning">Edit</button>
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus pesanan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #666;">Tidak ada pesanan ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div style="margin-top: 20px;">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Order Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>Buat Pesanan Baru</h3>
            <form method="POST" action="{{ route('admin.orders.store') }}">
                @csrf
                <div class="form-group">
                    <label>Nama Pelanggan *</label>
                    <input type="text" name="customer_name" required>
                </div>
                
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="customer_phone">
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="customer_address" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Produk *</label>
                    <div id="orderItems">
                        <div class="order-item">
                            <select name="items[0][product_id]" required>
                                <option value="">Pilih Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][quantity]" placeholder="Jumlah" min="1" required>
                            <button type="button" onclick="removeItem(this)" class="btn btn-danger">Hapus</button>
                        </div>
                    </div>
                    <button type="button" onclick="addItem()" class="btn btn-primary">Tambah Produk</button>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Buat Pesanan</button>
                    <button type="button" onclick="closeModal('createModal')" class="btn" style="background: #6c757d; color: white;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Order Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <h3>Edit Pesanan</h3>
            <form id="updateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Pelanggan *</label>
                    <input type="text" name="customer_name" id="edit_customer_name" required>
                </div>
                
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="customer_phone" id="edit_customer_phone">
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="customer_address" id="edit_customer_address" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Status Pembayaran</label>
                    <select name="payment_status" id="edit_payment_status">
                        <option value="pending">Pending</option>
                        <option value="paid">Dibayar</option>
                        <option value="cancel">Dibatalkan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status Pesanan</label>
                    <select name="order_status" id="edit_order_status">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="in_progress">Sedang Dibuat</option>
                        <option value="ready">Siap</option>
                        <option value="shipped">Dikirim</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Update Pesanan</button>
                    <button type="button" onclick="closeModal('updateModal')" class="btn" style="background: #6c757d; color: white;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = 1;

        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function openUpdateModal(orderId) {
            // Here you would fetch order data via AJAX, for now using placeholder
            // In a real implementation, you'd make an AJAX call to get order details
            document.getElementById('updateModal').style.display = 'block';
            document.getElementById('updateForm').action = '/admin/orders/' + orderId;
            
            // You would populate the form fields with order data here
            // For now, this is a simplified version
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function addItem() {
            const orderItems = document.getElementById('orderItems');
            const newItem = document.createElement('div');
            newItem.className = 'order-item';
            newItem.innerHTML = `
                <select name="items[${itemIndex}][product_id]" required>
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <input type="number" name="items[${itemIndex}][quantity]" placeholder="Jumlah" min="1" required>
                <button type="button" onclick="removeItem(this)" class="btn btn-danger">Hapus</button>
            `;
            orderItems.appendChild(newItem);
            itemIndex++;
        }

        function removeItem(button) {
            const orderItems = document.getElementById('orderItems');
            if (orderItems.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert('Minimal harus ada satu produk');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>