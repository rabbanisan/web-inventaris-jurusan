<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;


Route::get('/', [PublicCatalogController::class, 'index'])->name('catalog.home');

// Katalog publik: siapa pun (tanpa login) dapat melihat barang yang tersedia
Route::get('/katalog', [PublicCatalogController::class, 'index'])->name('katalog');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
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

// -------------------------------------------------------------------
// Admin routes – hanya dapat diakses oleh user dengan role "admin"
// -------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/users', [UserManagementController::class, 'index'])
        ->name('admin.users.index');

    Route::get('admin/users/create', [UserManagementController::class, 'create'])
        ->name('admin.users.create');

    Route::post('admin/users', [UserManagementController::class, 'store'])
        ->name('admin.users.store');

    Route::get('admin/users/{user}/edit', [UserManagementController::class, 'edit'])
        ->name('admin.users.edit');

    Route::put('admin/users/{user}', [UserManagementController::class, 'update'])
        ->name('admin.users.update');

    Route::delete('admin/users/{user}', [UserManagementController::class, 'destroy'])
        ->name('admin.users.destroy');
});

require __DIR__ . '/auth.php';
