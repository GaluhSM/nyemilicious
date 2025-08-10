<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = User::latest()->paginate(15);
        
        $stats = [
            'total_products' => Product::count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total'),
            'total_accounts' => User::count(),
        ];

        return view('admin.accounts.index', compact('accounts', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::log(Auth::user()->username, 'Membuat akun baru: ' . $user->username, 'User');

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil dibuat');
    }

    public function update(Request $request, User $account)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $account->id,
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $account->update($updateData);

        ActivityLog::log(Auth::user()->username, 'Mengupdate akun: ' . $account->username, 'User');

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil diupdate');
    }

    public function destroy(User $account)
    {
        // Prevent deleting own account
        if ($account->id === Auth::id()) {
            return redirect()->route('admin.accounts.index')->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $username = $account->username;
        $account->delete();

        ActivityLog::log(Auth::user()->username, 'Menghapus akun: ' . $username, 'User');

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil dihapus');
    }
}
