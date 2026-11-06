<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        // dd($user->role, $user->isAdmin());

        // User biasa (peminjam) mendapat dashboard sendiri
        if (! $user->isAdmin()) {
            return $this->userDashboard($user);
        }

        return Inertia::render('Dashboard', [
            'totalBarang' => Barang::count(),
            'totalUnitBarang' => (int) Barang::sum('jumlah'),
            'totalKategori' => Kategori::count(),
            'totalRuangan' => Ruangan::count(),
            'totalDipinjam' => (int) Peminjaman::where('status', 'approved')->sum('jumlah'),
            'peminjamanPending' => Peminjaman::where('status', 'pending')->count(),
            'barangTerbaru' => Barang::with(['kategori', 'ruangan'])
                ->latest()
                ->take(10)
                ->get(),
            'peminjamanTerbaru' => Peminjaman::with(['user', 'barang'])
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    private function userDashboard($user)
    {
        // dd('cabang user jalan');
        $milik = Peminjaman::where('user_id', $user->id);

        return Inertia::render('User/Dashboard', [
            'totalPeminjaman' => (clone $milik)->count(),
            'peminjamanPending' => (clone $milik)->where('status', 'pending')->count(),
            'sedangDipinjam' => (int) (clone $milik)->where('status', 'approved')->sum('jumlah'),
            'peminjamanTerbaru' => (clone $milik)
                ->with('barang:id,kode,nama')
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}
