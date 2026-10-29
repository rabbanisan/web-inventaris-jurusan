<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangController extends Controller
{
    public function index()
    {
        return Inertia::render('Barang/Index', [
            'barang' => Barang::with(['kategori', 'ruangan'])
                ->latest()
                ->paginate(15),
            'kategoriList' => Kategori::orderBy('nama')->get(),
            'ruanganList' => Ruangan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:barangs,kode',
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        Barang::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:barangs,kode,' . $barang->id,
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $barang->update($validated);

        return redirect()->back();
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->back();
    }
}
