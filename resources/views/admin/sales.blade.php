<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Penjualan - NYEMIL</title>
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
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; }
        .filter-btn { padding: 8px 15px; border: 1px solid #ddd; background: white; cursor: pointer; text-decoration: none; color: #333; }
        .filter-btn.active { background: #007bff; color: white; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: bold; }
        .chart-container { margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 4px; }
        .chart-bar { height: 20px; background: #007bff; margin: 5px 0; border-radius: 2px; position: relative; }
        .chart-label { font-weight: bold; margin-bottom: 5px; }
        .chart-percentage { position: absolute; right: 10px; top: 2px; color: white; font-size: 12px; }
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
                <a href="{{ route('admin.sales') }}" class="active">Ringkasan Penjualan</a>
                <a href="{{ route('admin.orders.index') }}">Pesanan</a>
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
            <h1>Ringkasan Penjualan</h1>

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

            <!-- Filters -->
            <div class="filters">
                <span style="font-weight: bold;">Filter:</span>
                <a href="{{ route('admin.sales') }}?filter=all" class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">Semua</a>
                <a href="{{ route('admin.sales') }}?filter=today" class="filter-btn {{ $filter == 'today' ? 'active' : '' }}">Hari Ini</a>
                <a href="{{ route('admin.sales') }}?filter=week" class="filter-btn {{ $filter == 'week' ? 'active' : '' }}">Minggu Ini</a>
                <a href="{{ route('admin.sales') }}?filter=month" class="filter-btn {{ $filter == 'month' ? 'active' : '' }}">Bulan Ini</a>
            </div>

            <!-- Sales Summary -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                <h3>Total Penjualan (Pesanan Selesai): Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                <p>Jumlah Pesanan Selesai: {{ $completedOrders->count() }}</p>
            </div>

            <!-- Completed Orders Table -->
            <h3>Pesanan yang Selesai</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Jenis Pengiriman</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedOrders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->delivery_type === 'pickup' ? 'Ambil di Bazar' : 'Antar ke Rumah' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">Tidak ada pesanan yang selesai</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Product Sales Chart -->
            <div class="chart-container">
                <h3>Diagram Penjualan Berdasarkan Produk</h3>
                @if($chartData->count() > 0)
                    @foreach($chartData as $data)
                    <div style="margin-bottom: 15px;">
                        <div class="chart-label">{{ $data['name'] }}</div>
                        <div class="chart-bar" style="width: {{ $data['percentage'] }}%;">
                            <span class="chart-percentage">{{ $data['percentage'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p style="color: #666; text-align: center;">Belum ada data penjualan produk</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>