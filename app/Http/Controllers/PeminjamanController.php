<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeminjamanController extends Controller
{
    /**
     * List semua peminjaman (admin view).
     */
    public function index()
    {
        return Inertia::render('Peminjaman/Index', [
            'peminjaman' => Peminjaman::with(['user', 'barang'])
                ->orderByDesc('created_at')
                ->paginate(15),
        ]);
    }

    /**
     * Simpan peminjaman baru (user atau admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id'      => 'required|exists:barangs,id',
            'jumlah'         => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'keterangan'     => 'nullable|string',
        ]);

        // Pastikan stok barang cukup
        $barang = Barang::findOrFail($validated['barang_id']);
        if ($validated['jumlah'] > $barang->jumlah) {
            return back()->withErrors(['jumlah' => 'Stok tidak mencukupi.']);
        }

        // Kurangi stok sementara (bisa di‑adjust saat status berubah)
        $barang->decrement('jumlah', $validated['jumlah']);

        Peminjaman::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'status'  => 'pending',
        ]));

        return redirect()->back();
    }

    /**
     * Update status atau data peminjaman (admin).
     */
    public function update(Request $request, Peminjaman $peminjaman)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,returned,rejected',
        ]);

        // Jika status berubah menjadi returned, kembalikan stok
        if ($peminjaman->status !== 'returned' && $validated['status'] === 'returned') {
            $peminjaman->barang->increment('jumlah', $peminjaman->jumlah);
        }

        $peminjaman->update($validated);

        return redirect()->back();
    }

    /**
     * Hapus peminjaman (admin).
     */
    public function destroy(Peminjaman $peminjaman)
    {
        // Kembalikan stok bila belum dikembalikan
        if ($peminjaman->status !== 'returned') {
            $peminjaman->barang->increment('jumlah', $peminjaman->jumlah);
        }

        $peminjaman->delete();

        return redirect()->back();
    }
}
