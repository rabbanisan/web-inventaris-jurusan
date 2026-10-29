<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RuanganController extends Controller
{
    public function index()
    {
        return Inertia::render('Ruangan/Index', [
            'ruangan' => Ruangan::withCount('barang')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:ruangans,nama',
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Ruangan::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:ruangans,nama,' . $ruangan->id,
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $ruangan->update($validated);

        return redirect()->back();
    }

    public function destroy(Ruangan $ruangan)
    {
        $ruangan->delete();

        return redirect()->back();
    }
}
