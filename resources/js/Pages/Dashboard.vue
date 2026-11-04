<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    totalBarang: { type: Number, default: 0 },
    totalUnitBarang: { type: Number, default: 0 },
    totalKategori: { type: Number, default: 0 },
    totalRuangan: { type: Number, default: 0 },
    totalDipinjam: { type: Number, default: 0 },
    peminjamanPending: { type: Number, default: 0 },
    barangTerbaru: { type: Array, default: () => [] },
    peminjamanTerbaru: { type: Array, default: () => [] },
});

const stats = computed(() => [
    {
        label: 'Total Barang',
        value: props.totalBarang,
        note: `${props.totalUnitBarang} unit fisik`,
        accent: 'text-blue-600',
    },
    {
        label: 'Jenis Data',
        value: props.totalKategori,
        note: 'kategori terdaftar',
        accent: 'text-indigo-600',
    },
    {
        label: 'Jumlah Ruangan',
        value: props.totalRuangan,
        note: 'ruangan terdaftar',
        accent: 'text-emerald-600',
    },
    {
        label: 'Sedang Dipinjam',
        value: props.totalDipinjam,
        note: `${props.peminjamanPending} menunggu persetujuan`,
        accent: 'text-amber-600',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Kartu ringkasan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="item in stats"
                        :key="item.label"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
                    >
                        <p class="text-xs font-medium text-gray-500 uppercase">{{ item.label }}</p>
                        <p class="mt-2 text-2xl font-semibold" :class="item.accent">{{ item.value }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ item.note }}</p>
                    </div>
                </div>

                <!-- Peminjaman terbaru -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-800">Peminjaman Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!peminjamanTerbaru.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data peminjaman</td>
                                </tr>
                                <tr v-for="item in peminjamanTerbaru" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ item.user?.name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.barang?.kode ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.jumlah }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ item.status }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(item.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Barang terbaru -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-800">Barang Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!barangTerbaru.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data barang</td>
                                </tr>
                                <tr v-for="item in barangTerbaru" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ item.kode }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ item.nama }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.kategori?.nama ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.ruangan?.nama ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'bg-green-100 text-green-800': item.kondisi === 'baik',
                                            'bg-yellow-100 text-yellow-800': item.kondisi === 'rusak_ringan',
                                            'bg-red-100 text-red-800': item.kondisi === 'rusak_berat',
                                        }" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ item.kondisi?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.jumlah }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
