<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - NYEMIL</title>
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
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: bold; }
        .log-entry { margin-bottom: 10px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .log-header { display: flex; justify-content: space-between; align-items: center; font-weight: bold; }
        .log-name { color: #007bff; }
        .log-time { color: #666; font-size: 0.9em; }
        .log-activity { margin-top: 5px; color: #333; }
        .log-model { margin-top: 5px; color: #666; font-size: 0.9em; }
        .log-data { margin-top: 10px; background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9em; overflow-x: auto; }
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
                <a href="{{ route('admin.orders.index') }}">Pesanan</a>
                <a href="{{ route('admin.accounts.index') }}">Akun</a>
                <a href="{{ route('admin.logs') }}" class="active">Log Aktivitas</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin: 20px;">
                @csrf
                <button type="submit" class="logout-btn" style="width: 100%; border: none; cursor: pointer;">Logout</button>
            </form>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Log Aktivitas</h1>

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

            <!-- Logs -->
            <div style="margin-bottom: 20px;">
                <h3>Riwayat Aktivitas ({{ $logs->total() }} total)</h3>
                <p style="color: #666; font-size: 0.9em;">
                    Menampilkan semua aktivitas CRUD yang dilakukan di sistem admin dan user
                </p>
            </div>

            @forelse($logs as $log)
            <div class="log-entry">
                <div class="log-header">
                    <div>
                        <span class="log-name">{{ $log->name }}</span>
                        @if($log->model)
                            <span class="log-model">- {{ $log->model }}</span>
                        @endif
                    </div>
                    <div class="log-time">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                
                <div class="log-activity">{{ $log->activity }}</div>
                
                @if($log->data && count($log->data) > 0)
                <div class="log-data">
                    <strong>Data terkait:</strong>
                    <pre style="margin: 5px 0 0 0; white-space: pre-wrap;">{{ json_encode($log->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif
            </div>
            @empty
            <div style="text-align: center; color: #666; padding: 50px 0;">
                <h3>Belum Ada Log Aktivitas</h3>
                <p>Log aktivitas akan muncul ketika ada aktivitas CRUD di sistem</p>
            </div>
            @endforelse

            <!-- Pagination -->
            @if($logs->hasPages())
                <div style="margin-top: 20px;">
                    {{ $logs->links() }}
                </div>
            @endif

            <!-- Info Box -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
                <h4 style="margin-top: 0;">Keterangan Log:</h4>
                <ul style="margin: 0;">
                    <li><strong>Nama "sistem":</strong> Aktivitas yang dilakukan dari halaman user (seperti pembuatan pesanan)</li>
                    <li><strong>Nama username:</strong> Aktivitas yang dilakukan oleh admin yang login</li>
                    <li><strong>Model:</strong> Jenis data yang dioperasi (Order, User, Product, dll)</li>
                    <li><strong>Data terkait:</strong> Detail data yang dioperasi (jika ada)</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>