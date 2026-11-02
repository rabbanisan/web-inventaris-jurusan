<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\RuanganController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', [PublicCatalogController::class, 'index'])->name('catalog.home');

// Katalog publik: siapa pun (tanpa login) dapat melihat barang yang tersedia
Route::get('/katalog', [PublicCatalogController::class, 'index'])->name('katalog');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User biasa (peminjam) boleh mengajukan peminjaman, admin tetap bisa lewat grup di bawah
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
});

// Route khusus Petugas Inventaris (Admin)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('barang/import', [BarangController::class, 'import'])->name('barang.import');
    Route::get('barang/template', [BarangController::class, 'template'])->name('barang.template');
    Route::resource('barang', BarangController::class)->except(['create', 'show', 'edit']);

    Route::resource('kategori', KategoriController::class)->except(['create', 'show', 'edit']);

    Route::post('ruangan/import', [RuanganController::class, 'import'])->name('ruangan.import');
    Route::get('ruangan/template', [RuanganController::class, 'template'])->name('ruangan.template');
    Route::resource('ruangan', RuanganController::class)->except(['create', 'show', 'edit']);
    Route::patch('peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
    Route::patch('peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
    Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'update', 'destroy']);
});

require __DIR__ . '/auth.php';
