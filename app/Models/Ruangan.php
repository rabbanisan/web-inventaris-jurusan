<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    protected $fillable = ['nama', 'lokasi', 'keterangan'];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }
}
