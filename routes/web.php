<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuanganController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User biasa (peminjam) boleh mengajukan peminjaman, admin tetap bisa lewat grup di bawah
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
});

// Route khusus Petugas Inventaris (Admin)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('barang', BarangController::class)->except(['create', 'show', 'edit']);
    Route::resource('kategori', KategoriController::class)->except(['create', 'show', 'edit']);
    Route::resource('ruangan', RuanganController::class)->except(['create', 'show', 'edit']);
    Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'update', 'destroy']);
});

require __DIR__ . '/auth.php';
