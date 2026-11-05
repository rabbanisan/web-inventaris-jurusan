<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanStoreRequest;
use App\Http\Requests\PeminjamanUpdateRequest;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($validated, $request) {
            // lockForUpdate mencegah dua request bersamaan lolos cek stok yang sama.
            $barang = Barang::lockForUpdate()->findOrFail($validated['barang_id']);

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
        });

        return redirect()->back();
    }

    /**
     * Update status atau data peminjaman (admin).
     */
    public function update(PeminjamanUpdateRequest $request, Peminjaman $peminjaman)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $peminjaman) {
            $statusMenahanStok = ['pending', 'approved'];
            $statusBaru = $validated['status'] ?? $peminjaman->status;

            $duluMenahanStok = in_array($peminjaman->status, $statusMenahanStok, true);
            $kiniMenahanStok = in_array($statusBaru, $statusMenahanStok, true);

            $barang = $peminjaman->barang()->lockForUpdate()->first();

            if (! $barang) {
                throw ValidationException::withMessages([
                    'status' => 'Barang untuk peminjaman ini sudah tidak ada.',
                ]);
            }

            if ($duluMenahanStok && ! $kiniMenahanStok) {
                // Status menjadi returned/rejected → stok dikembalikan
                $barang->increment('jumlah', $peminjaman->jumlah);
            } elseif (! $duluMenahanStok && $kiniMenahanStok) {
                // Peminjaman diaktifkan kembali → stok harus masih cukup
                if ($barang->jumlah < $peminjaman->jumlah) {
                    throw ValidationException::withMessages([
                        'status' => 'Stok barang tidak mencukupi untuk mengaktifkan kembali peminjaman ini.',
                    ]);
                }

                $barang->decrement('jumlah', $peminjaman->jumlah);
            }

            $peminjaman->update($validated);
        });

        return redirect()->back();
    }

    /**
     * Setujui peminjaman (admin).
     *
     * Stok sudah dikurangi saat pengajuan dibuat, jadi approval hanya mengubah status.
     */
    public function approve(Peminjaman $peminjaman)
    {
        DB::transaction(function () use ($peminjaman) {
            // Baca ulang dengan row lock agar dua admin tidak memproses data yang sama.
            $peminjaman = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($peminjaman->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya peminjaman berstatus pending yang dapat disetujui.',
                ]);
            }

            $peminjaman->update(['status' => 'approved']);
        });

        return redirect()->back();
    }

    /**
     * Tolak peminjaman (admin) dan kembalikan stok barang.
     */
    public function reject(Peminjaman $peminjaman)
    {
        DB::transaction(function () use ($peminjaman) {
            $peminjaman = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if (! in_array($peminjaman->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Peminjaman ini sudah selesai diproses.',
                ]);
            }

            $peminjaman->barang()->lockForUpdate()->first()?->increment('jumlah', $peminjaman->jumlah);

            $peminjaman->update(['status' => 'rejected']);
        });

        return redirect()->back();
    }

    /**
     * Hapus peminjaman (admin).
     */
    public function destroy(Peminjaman $peminjaman)
    {
        DB::transaction(function () use ($peminjaman) {
            $peminjaman = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            // Kembalikan stok hanya bila peminjaman masih menahan stok
            if (in_array($peminjaman->status, ['pending', 'approved'], true)) {
                $peminjaman->barang()->lockForUpdate()->first()?->increment('jumlah', $peminjaman->jumlah);
            }

            $peminjaman->delete();
        });

        return redirect()->back();
    }
}
