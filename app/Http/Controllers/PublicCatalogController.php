<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicCatalogController extends Controller
{
    /**
     * Katalog publik: daftar barang tersedia, dapat diakses tanpa login.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'kategori_id']);

        $barang = Barang::with(['kategori', 'ruangan'])
            ->where('jumlah', '>', 0)
            ->when($filters['q'] ?? null, function ($query, string $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('kode', 'like', "%{$q}%");
                });
            })
            ->when($filters['kategori_id'] ?? null, fn ($query, $id) => $query->where('kategori_id', $id))
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Index', [
            'barang' => $barang,
            'kategoriList' => Kategori::orderBy('nama')->get(['id', 'nama']),
            'filters' => [
                'q' => $filters['q'] ?? null,
                'kategori_id' => $filters['kategori_id'] ?? null,
            ],
        ]);
    }
}
