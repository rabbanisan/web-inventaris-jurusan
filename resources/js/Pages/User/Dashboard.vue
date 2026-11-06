<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    totalPeminjaman: { type: Number, default: 0 },
    peminjamanPending: { type: Number, default: 0 },
    sedangDipinjam: { type: Number, default: 0 },
    peminjamanTerbaru: { type: Array, default: () => [] },
});

const stats = computed(() => [
    {
        label: "Total Pengajuan",
        value: props.totalPeminjaman,
        accent: "text-blue-600",
    },
    {
        label: "Menunggu Persetujuan",
        value: props.peminjamanPending,
        accent: "text-amber-600",
    },
    {
        label: "Sedang Dipinjam",
        value: props.sedangDipinjam,
        accent: "text-emerald-600",
    },
]);

const formatTanggal = (d) =>
    new Date(d).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>Dashboard</template>

        <div class="mx-auto max-w-5xl space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div
                    v-for="s in stats"
                    :key="s.label"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
                >
                    <p class="text-xs font-medium text-gray-500 uppercase">
                        {{ s.label }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold" :class="s.accent">
                        {{ s.value }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div
                    class="px-6 py-4 border-b border-gray-200 flex items-center justify-between"
                >
                    <h3 class="text-sm font-semibold text-gray-800">
                        Peminjaman Saya
                    </h3>
                    <Link
                        href="/user/katalog"
                        class="text-sm text-blue-600 hover:underline"
                    >
                        Lihat katalog
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Barang
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Jumlah
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Tanggal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="!peminjamanTerbaru.length">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-gray-400"
                                >
                                    Belum ada pengajuan peminjaman
                                </td>
                            </tr>
                            <tr
                                v-for="p in peminjamanTerbaru"
                                :key="p.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ p.barang?.nama ?? "-" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ p.jumlah }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-500 capitalize"
                                >
                                    {{ p.status }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatTanggal(p.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
