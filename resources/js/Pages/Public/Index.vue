<script setup>
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    barang: Object,
    kategoriList: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const q = ref(props.filters?.q ?? "");
const kategoriId = ref(props.filters?.kategori_id ?? "");

function applyFilter() {
    router.get(
        "/katalog",
        {
            q: q.value || undefined,
            kategori_id: kategoriId.value || undefined,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}

function resetFilter() {
    q.value = "";
    kategoriId.value = "";
    applyFilter();
}

const showModal = ref(false);
const selected = ref(null);
const successMessage = ref("");

const form = useForm({
    barang_id: null,
    jumlah: 1,
    tanggal_pinjam: "",
    keterangan: "",
});

const maxJumlah = computed(() => selected.value?.jumlah ?? 1);

function today() {
    return new Date().toISOString().slice(0, 10);
}

function openModal(item) {
    selected.value = item;
    form.reset();
    form.clearErrors();
    form.barang_id = item.id;
    form.jumlah = 1;
    form.tanggal_pinjam = today();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selected.value = null;
}

function submit() {
    form.post("/peminjaman", {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value =
                "Pengajuan peminjaman terkirim dan menunggu persetujuan admin.";
            closeModal();
            router.reload({ only: ["barang"] });
        },
    });
}

const kondisiLabel = {
    baik: "Baik",
    rusak_ringan: "Rusak Ringan",
    rusak_berat: "Rusak Berat",
};
</script>

<template>
    <Head title="Katalog Barang" />

    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
            >
                <Link href="/katalog" class="flex items-center space-x-2">
                    <span class="text-lg font-bold text-gray-800"
                        >Inventaris Barang</span
                    >
                </Link>
                <nav class="flex items-center space-x-3 text-sm">
                    <template v-if="user">
                        <Link
                            href="/dashboard"
                            class="px-3 py-2 text-gray-700 hover:text-gray-900 font-medium"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="px-3 py-2 text-gray-700 hover:text-gray-900 font-medium"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Judul & pencarian -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Barang Tersedia
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Lihat barang inventaris yang siap dipinjam. Ajukan
                    peminjaman dan tunggu persetujuan admin.
                </p>
            </div>

            <div
                v-if="successMessage"
                class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            >
                {{ successMessage }}
            </div>

            <form
                class="mb-6 flex flex-col sm:flex-row sm:items-center gap-2"
                @submit.prevent="applyFilter"
            >
                <input
                    v-model="q"
                    type="text"
                    placeholder="Cari nama atau kode barang..."
                    class="w-full sm:w-72 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <select
                    v-model="kategoriId"
                    class="w-full sm:w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Semua kategori</option>
                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">
                        {{ k.nama }}
                    </option>
                </select>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                >
                    Cari
                </button>
                <button
                    type="button"
                    class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50"
                    @click="resetFilter"
                >
                    Reset
                </button>
            </form>

            <!-- Grid produk -->
            <div
                v-if="barang?.data?.length"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <article
                    v-for="item in barang.data"
                    :key="item.id"
                    class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col"
                >
                    <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                        <img
                            v-if="item.gambar_url"
                            :src="item.gambar_url"
                            :alt="item.nama"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-3xl font-bold text-gray-300"
                        >
                            {{ item.nama?.charAt(0)?.toUpperCase() }}
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h2
                                    class="text-sm font-semibold text-gray-900 leading-snug"
                                >
                                    {{ item.nama }}
                                </h2>
                                <span
                                    class="shrink-0 text-xs font-mono text-gray-400"
                                    >{{ item.kode }}</span
                                >
                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ item.kategori?.nama ?? "-" }} &middot;
                                {{ item.ruangan?.nama ?? "-" }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            item.kondisi === 'baik',
                                        'bg-yellow-100 text-yellow-800':
                                            item.kondisi === 'rusak_ringan',
                                        'bg-red-100 text-red-800':
                                            item.kondisi === 'rusak_berat',
                                    }"
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                >
                                    {{
                                        kondisiLabel[item.kondisi] ??
                                        item.kondisi
                                    }}
                                </span>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700"
                                >
                                    Stok: {{ item.jumlah }}
                                </span>
                            </div>

                            <p
                                v-if="item.keterangan"
                                class="mt-3 text-xs text-gray-500 line-clamp-3"
                            >
                                {{ item.keterangan }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <button
                                v-if="user"
                                type="button"
                                class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                                @click="openModal(item)"
                            >
                                Ajukan Peminjaman
                            </button>
                            <Link
                                v-else
                                href="/login"
                                class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200"
                            >
                                Masuk untuk meminjam
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400"
            >
                Belum ada barang yang tersedia.
            </div>

            <!-- Pagination -->
            <div
                v-if="barang?.links?.length > 3"
                class="mt-8 flex justify-center space-x-1"
            >
                <template v-for="link in barang.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        :class="
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50'
                        "
                        class="px-3 py-1 text-sm border border-gray-300 rounded"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-3 py-1 text-sm text-gray-400"
                    />
                </template>
            </div>
        </main>
    </div>

    <!-- Modal pengajuan peminjaman -->
    <Teleport to="body">
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
            <div
                class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-800 mb-1">
                    Ajukan Peminjaman
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    {{ selected?.nama }} &middot; stok tersedia {{ maxJumlah }}
                </p>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Jumlah</label
                        >
                        <input
                            v-model="form.jumlah"
                            type="number"
                            min="1"
                            :max="maxJumlah"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p
                            v-if="form.errors.jumlah"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.jumlah }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Tanggal Pinjam</label
                        >
                        <input
                            v-model="form.tanggal_pinjam"
                            type="date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p
                            v-if="form.errors.tanggal_pinjam"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.tanggal_pinjam }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Keterangan</label
                        >
                        <textarea
                            v-model="form.keterangan"
                            rows="3"
                            placeholder="Keperluan peminjaman (opsional)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        <p
                            v-if="form.errors.keterangan"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.keterangan }}
                        </p>
                    </div>
                    <p
                        v-if="form.errors.barang_id"
                        class="text-red-500 text-xs"
                    >
                        {{ form.errors.barang_id }}
                    </p>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button
                            type="button"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                            @click="closeModal"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Mengirim..."
                                    : "Kirim Pengajuan"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
