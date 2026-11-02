<?php

namespace App\Http\Controllers;

use App\Exports\SpreadsheetTemplate;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

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

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:8192'],
            'images' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ], [], ['file' => 'file', 'images' => 'file gambar']);

        try {
            $result = (new BarangImport)->importFromUpload(
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
        return SpreadsheetTemplate::download('template-import-barang.xlsx', [
            'kode', 'nama', 'kategori', 'ruangan', 'jumlah', 'kondisi', 'keterangan', 'gambar',
        ], [
            ['BRG-001', 'Laptop Asus', 'Elektronik', 'Ruang Laboratorium', 1, 'baik', 'Contoh baris, silakan dihapus', 'laptop-asus.jpg'],
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
