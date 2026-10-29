<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function index()
    {
        return Inertia::render('Kategori/Index', [
            'kategori' => Kategori::withCount('barang')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama',
            'keterangan' => 'nullable|string',
        ]);

        Kategori::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama,' . $kategori->id,
            'keterangan' => 'nullable|string',
        ]);

        $kategori->update($validated);

        return redirect()->back();
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->back();
    }
}
