<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanStoreRequest;
use App\Http\Requests\PeminjamanUpdateRequest;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Validation\ValidationException;
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
            'barangList' => Barang::where('jumlah', '>', 0)
                ->orderBy('nama')
                ->get(['id', 'kode', 'nama', 'jumlah']),
        ]);
    }

    /**
     * Simpan peminjaman baru (user atau admin).
     */
    public function store(PeminjamanStoreRequest $request)
    {
        $validated = $request->validated();

        $barang = Barang::findOrFail($validated['barang_id']);
        if ($validated['jumlah'] > $barang->jumlah) {
            throw ValidationException::withMessages([
                'jumlah' => 'Stok tidak mencukupi.',
            ]);
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
    public function update(PeminjamanUpdateRequest $request, Peminjaman $peminjaman)
    {
        $validated = $request->validated();

        $statusMenahanStok = ['pending', 'approved'];
        $duluMenahanStok = in_array($peminjaman->status, $statusMenahanStok, true);
        $kiniMenahanStok = in_array($validated['status'], $statusMenahanStok, true);

        if ($duluMenahanStok && ! $kiniMenahanStok) {
            // Status menjadi returned/rejected → stok dikembalikan
            $peminjaman->barang->increment('jumlah', $peminjaman->jumlah);
        } elseif (! $duluMenahanStok && $kiniMenahanStok) {
            // Peminjaman diaktifkan kembali → stok harus masih cukup
            if ($peminjaman->barang->jumlah < $peminjaman->jumlah) {
                throw ValidationException::withMessages([
                    'status' => 'Stok barang tidak mencukupi untuk mengaktifkan kembali peminjaman ini.',
                ]);
            }

            $peminjaman->barang->decrement('jumlah', $peminjaman->jumlah);
        }

        $peminjaman->update($validated);

        return redirect()->back();
    }

    /**
     * Hapus peminjaman (admin).
     */
    public function destroy(Peminjaman $peminjaman)
    {
        // Kembalikan stok hanya bila peminjaman masih menahan stok
        if (in_array($peminjaman->status, ['pending', 'approved'], true)) {
            $peminjaman->barang->increment('jumlah', $peminjaman->jumlah);
        }

        $peminjaman->delete();

        return redirect()->back();
    }
}
