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
        $isAdmin = $user?->isAdmin() ?? false;

        return Inertia::render('Dashboard', [
            // Jumlah record barang (jenis entri barang)
            'totalBarang' => Barang::count(),
            // Total unit fisik dari seluruh barang
            'totalUnitBarang' => (int) Barang::sum('jumlah'),
            // Jenis data (kategori)
            'totalKategori' => Kategori::count(),
            // Jumlah ruangan
            'totalRuangan' => Ruangan::count(),
            // Unit yang sedang dipinjam (status approved)
            'totalDipinjam' => (int) Peminjaman::where('status', 'approved')->sum('jumlah'),
            // Pengajuan yang menunggu persetujuan
            'peminjamanPending' => Peminjaman::where('status', 'pending')->count(),
            'barangTerbaru' => Barang::with(['kategori', 'ruangan'])
                ->latest()
                ->take(10)
                ->get(),
            // Admin melihat semua; user biasa hanya melihat pengajuannya sendiri
            // agar data peminjaman pengguna lain tidak bocor.
            'peminjamanTerbaru' => Peminjaman::with(['user', 'barang'])
                ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user->id))
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}
