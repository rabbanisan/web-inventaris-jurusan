<?php

namespace App\Http\Controllers;

use App\Exports\SpreadsheetTemplate;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'gambar' => 'gambar barang',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        }

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
            // min:0 karena stok bisa 0 saat semua unit sedang dipinjam;
            // admin tetap harus bisa menyunting data barang tersebut.
            'jumlah' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ], [], [
            'gambar' => 'gambar barang',
        ]);

        if ($request->hasFile('gambar')) {
            $this->hapusFileGambar($barang);
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            $this->hapusFileGambar($barang);
            $validated['gambar'] = null;
        }

        // hapus_gambar hanya penanda untuk controller, bukan kolom di tabel barangs.
        unset($validated['hapus_gambar']);

        $barang->update($validated);

        return redirect()->back();
    }

    public function destroy(Barang $barang)
    {
        $this->hapusFileGambar($barang);

        $barang->delete();

        return redirect()->back();
    }

    /**
     * Hapus file gambar barang dari storage (bila memang file lokal, bukan URL eksternal).
     */
    private function hapusFileGambar(Barang $barang): void
    {
        if (! $barang->gambar) {
            return;
        }

        if (str_starts_with($barang->gambar, 'http://') || str_starts_with($barang->gambar, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($barang->gambar);
    }
}
