<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('barangs', 'gambar')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->string('gambar')->nullable()->after('keterangan');
            });
        }

        if (! Schema::hasColumn('ruangans', 'gambar')) {
            Schema::table('ruangans', function (Blueprint $table) {
                $table->string('gambar')->nullable()->after('keterangan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('barangs', 'gambar')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('gambar');
            });
        }

        if (Schema::hasColumn('ruangans', 'gambar')) {
            Schema::table('ruangans', function (Blueprint $table) {
                $table->dropColumn('gambar');
            });
        }
    }
};
