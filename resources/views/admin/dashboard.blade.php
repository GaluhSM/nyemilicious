<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - NYEMIL</title>
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
                <a href="{{ route('admin.dashboard') }}" class="active">Dashboard</a>
                <a href="{{ route('admin.sales') }}">Ringkasan Penjualan</a>
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
            <h1>Dashboard Admin</h1>
            
            @if(session('success'))
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    {{ session('success') }}
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

            <!-- Quick Actions -->
            <div style="margin-top: 30px;">
                <h3>Quick Actions</h3>
                <div style="display: flex; gap: 15px;">
                    <a href="{{ route('admin.orders.index') }}" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Lihat Pesanan</a>
                    <a href="{{ route('admin.sales') }}" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">Laporan Penjualan</a>
                    <a href="{{ route('admin.accounts.index') }}" style="padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 4px;">Kelola Akun</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>