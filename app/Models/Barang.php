<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Barang extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'kategori_id',
        'ruangan_id',
        'kondisi',
        'jumlah',
        'keterangan',
        'gambar',
    ];

    protected $appends = ['gambar_url'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * URL gambar siap pakai di frontend (mendukung URL eksternal maupun file storage).
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        $gambar = (string) $this->gambar;

        if (str_starts_with($gambar, 'http://') || str_starts_with($gambar, 'https://')) {
            return $gambar;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($gambar);
    }
}
