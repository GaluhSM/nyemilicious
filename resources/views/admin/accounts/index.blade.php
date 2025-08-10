<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - NYEMIL</title>
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
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:hover { opacity: 0.9; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
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
                <a href="{{ route('admin.accounts.index') }}" class="active">Akun</a>
                <a href="{{ route('admin.logs') }}">Log Aktivitas</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin: 20px;">
                @csrf
                <button type="submit" class="logout-btn" style="width: 100%; border: none; cursor: pointer;">Logout</button>
            </form>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Kelola Akun</h1>

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

            @if($errors->any())
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

            <!-- Create Account Button -->
            <button onclick="openCreateModal()" class="btn btn-success" style="margin-bottom: 20px;">+ Buat Akun Baru</button>

            <!-- Accounts Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr>
                        <td>{{ $account->id }}</td>
                        <td>{{ $account->email }}</td>
                        <td>{{ $account->username }}</td>
                        <td>{{ $account->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button onclick="openUpdateModal({{ $account->id }}, '{{ $account->username }}')" class="btn btn-warning">Edit</button>
                            @if($account->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">Tidak ada akun ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($accounts->hasPages())
                <div style="margin-top: 20px;">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Account Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>Buat Akun Baru</h3>
            <form method="POST" action="{{ route('admin.accounts.store') }}">
                @csrf
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Buat Akun</button>
                    <button type="button" onclick="closeModal('createModal')" class="btn" style="background: #6c757d; color: white;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Account Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <h3>Edit Akun</h3>
            <form id="updateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                
                <div class="form-group">
                    <label>Password (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" id="edit_password">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Update Akun</button>
                    <button type="button" onclick="closeModal('updateModal')" class="btn" style="background: #6c757d; color: white;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function openUpdateModal(accountId, username) {
            document.getElementById('updateModal').style.display = 'block';
            document.getElementById('updateForm').action = '/admin/accounts/' + accountId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = '';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
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