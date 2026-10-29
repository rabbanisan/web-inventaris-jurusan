<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard', [
            'totalBarang' => Barang::count(),
            'totalKategori' => Kategori::count(),
            'totalRuangan' => Ruangan::count(),
            'barangTerbaru' => Barang::with(['kategori', 'ruangan'])
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}
