<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangans')->cascadeOnDelete();
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->integer('jumlah')->default(1);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
