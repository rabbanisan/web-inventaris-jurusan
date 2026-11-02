<?php

namespace App\Http\Controllers;

use App\Exports\SpreadsheetTemplate;
use App\Imports\RuanganImport;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class RuanganController extends Controller
{
    public function index()
    {
        return Inertia::render('Ruangan/Index', [
            'ruangan' => Ruangan::withCount('barang')->orderBy('nama')->get(),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:8192'],
            'images' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ], [], ['file' => 'file', 'images' => 'file gambar']);

        try {
            $result = (new RuanganImport)->importFromUpload(
                $request->file('file'),
                $request->file('images'),
            );
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'File tidak dapat dibaca: '.$e->getMessage(),
            ], 422);
        }

        return response()->json($result);
    }

    public function template()
    {
        return SpreadsheetTemplate::download('template-import-ruangan.xlsx', [
            'nama', 'lokasi', 'keterangan', 'gambar',
        ], [
            ['Ruang Laboratorium', 'Gedung A Lantai 2', 'Contoh baris, silakan dihapus', 'ruang-lab.jpg'],
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
