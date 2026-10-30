<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_peminjaman_and_stock_decreases()
    {
        $user = User::factory()->create(['role' => 'user']);
        $barang = Barang::factory()->create(['jumlah' => 5]);

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'barang_id'      => $barang->id,
            'jumlah'         => 2,
            'tanggal_pinjam'=> now()->addDay()->toDateString(),
            'keterangan'     => 'Untuk tugas',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('peminjamen', [
            'user_id'   => $user->id,
            'barang_id' => $barang->id,
            'jumlah'    => 2,
            'status'    => 'pending',
        ]);

        $this->assertEquals(3, $barang->fresh()->jumlah);
    }

    /** @test */
    public function admin_can_approve_and_return_peminjaman_and_stock_updates()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $barang = Barang::factory()->create(['jumlah' => 5]);

        // buat peminjaman (status pending, stok sudah dikurangi)
        $peminjaman = Peminjaman::create([
            'user_id'        => $admin->id,
            'barang_id'      => $barang->id,
            'jumlah'        => 2,
            'tanggal_pinjam'=> now()->toDateString(),
            'status'        => 'pending',
        ]);
        $barang->decrement('jumlah', 2);

        // admin approve
        $this->actingAs($admin)->put(route('peminjaman.update', $peminjaman), [
            'status' => 'approved',
        ])->assertRedirect();

        $this->assertEquals(3, $barang->fresh()->jumlah);

        // admin return
        $this->actingAs($admin)->put(route('peminjaman.update', $peminjaman), [
            'status' => 'returned',
        ])->assertRedirect();

        $this->assertEquals(5, $barang->fresh()->jumlah);
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('peminjaman.index'))->assertStatus(403);
        $this->actingAs($user)->put(route('peminjaman.update', 1), [ 'status' => 'approved' ])->assertStatus(403);
        $this->actingAs($user)->delete(route('peminjaman.destroy', 1))->assertStatus(403);
    }
}
