<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useImport } from '@/Composables/useImport';

const props = defineProps({
    barang: Object,
    kategoriList: Array,
    ruanganList: Array,
    filters: Object,
});

const showModal = ref(false);
const editMode = ref(false);
const showImportModal = ref(false);

const {
    file: importFile,
    processing: importProcessing,
    result: importResult,
    error: importError,
    reset: resetImport,
    onFileChange: onImportFileChange,
    onZipChange: onImportZipChange,
    submit: submitImport,
} = useImport({
    url: '/barang/import',
    reloadProps: ['barang'],
});

function openImport() {
    resetImport();
    showImportModal.value = true;
}

const form = useForm({
    id: null,
    nama: '',
    kode: '',
    kategori_id: '',
    ruangan_id: '',
    kondisi: 'baik',
    jumlah: 1,
    keterangan: '',
    gambar: null,
    hapus_gambar: false,
});

function openCreate() {
    form.reset();
    editMode.value = false;
    showModal.value = true;
}

function openEdit(item) {
    form.id = item.id;
    form.nama = item.nama;
    form.kode = item.kode;
    form.kategori_id = item.kategori_id;
    form.ruangan_id = item.ruangan_id;
    form.kondisi = item.kondisi;
    form.jumlah = item.jumlah;
    form.keterangan = item.keterangan || '';
    form.gambar = null;
    form.hapus_gambar = false;
    editMode.value = true;
    showModal.value = true;
}

function onGambarChange(event) {
    form.gambar = event.target.files?.[0] ?? null;
}

function submit() {
    const options = {
        forceFormData: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    };

    if (editMode.value) {
        form.put(`/barang/${form.id}`, options);
    } else {
        form.post('/barang', options);
    }
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus barang ini?')) {
        router.delete(`/barang/${id}`);
    }
}
</script>

<template>
    <Head title="Barang" />
    <AppLayout>
        <template #header>Data Barang</template>

        <div class="mb-4 flex justify-between items-center">
            <div></div>
            <div class="flex items-center space-x-2">
                <button @click="openImport" class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                    Import Excel
                </button>
                <button @click="openCreate" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    + Tambah Barang
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gambar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="!barang?.data?.length">
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400">Belum ada data barang</td>
                        </tr>
                        <tr v-for="item in barang?.data" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <img
                                    v-if="item.gambar_url"
                                    :src="item.gambar_url"
                                    :alt="item.nama"
                                    class="h-12 w-12 rounded-lg object-cover border border-gray-200"
                                />
                                <span v-else class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                    -
                                </span>
                            </td>
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
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button @click="openEdit(item)" class="text-blue-600 hover:text-blue-800">Edit</button>
                                <button @click="destroy(item.id)" class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="barang?.links?.length > 3" class="px-6 py-3 border-t border-gray-200 flex justify-center space-x-1">
                <template v-for="link in barang.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-3 py-1 text-sm border rounded"
                    />
                    <span v-else v-html="link.label" class="px-3 py-1 text-sm text-gray-400" />
                </template>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        {{ editMode ? 'Edit Barang' : 'Tambah Barang' }}
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                                <input v-model="form.kode" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                <p v-if="form.errors.kode" class="text-red-500 text-xs mt-1">{{ form.errors.kode }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                                <input v-model="form.nama" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select v-model="form.kategori_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <p v-if="form.errors.kategori_id" class="text-red-500 text-xs mt-1">{{ form.errors.kategori_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                                <select v-model="form.ruangan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Ruangan --</option>
                                    <option v-for="r in ruanganList" :key="r.id" :value="r.id">{{ r.nama }}</option>
                                </select>
                                <p v-if="form.errors.ruangan_id" class="text-red-500 text-xs mt-1">{{ form.errors.ruangan_id }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                                <select v-model="form.kondisi" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                <input v-model="form.jumlah" type="number" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                <p v-if="form.errors.jumlah" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea v-model="form.keterangan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Barang</label>
                            <div v-if="editMode && form.id" class="mb-2 flex items-center space-x-3">
                                <template v-if="!form.hapus_gambar">
                                    <img
                                        v-if="barang?.data?.find((i) => i.id === form.id)?.gambar_url"
                                        :src="barang.data.find((i) => i.id === form.id).gambar_url"
                                        alt="Gambar barang"
                                        class="h-16 w-16 rounded-lg object-cover border border-gray-200"
                                    />
                                </template>
                                <label class="inline-flex items-center space-x-2 text-xs text-gray-600">
                                    <input v-model="form.hapus_gambar" type="checkbox" class="rounded border-gray-300" />
                                    <span>Hapus gambar saat disimpan</span>
                                </label>
                            </div>
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-gray-100 file:text-sm file:text-gray-700"
                                @change="onGambarChange"
                            />
                            <p class="text-xs text-gray-400 mt-1">Format JPG/PNG/WEBP, maksimal 2 MB.</p>
                            <p v-if="form.errors.gambar" class="text-red-500 text-xs mt-1">{{ form.errors.gambar }}</p>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Modal Import Excel -->
        <Teleport to="body">
            <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showImportModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Import Barang dari Excel</h3>
                    <p class="text-xs text-gray-500 mb-2">
                        Kolom wajib: <span class="font-mono">kode, nama, kategori, ruangan</span>.
                        Kolom opsional: <span class="font-mono">jumlah, kondisi, keterangan</span>.
                        Baris dengan kode yang sudah ada akan diperbarui; kategori &amp; ruangan baru dibuat otomatis.
                    </p>
                    <a href="/barang/template" class="inline-block text-sm text-blue-600 hover:text-blue-800 mb-3">
                        Unduh template Excel
                    </a>
                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">File Excel / CSV</label>
                            <input
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="onImportFileChange"
                                class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-gray-100 file:text-sm file:text-gray-700"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">File Gambar (.zip, opsional)</label>
                            <input
                                type="file"
                                accept=".zip"
                                @change="onImportZipChange"
                                class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-gray-100 file:text-sm file:text-gray-700"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Nama file di dalam ZIP harus sama dengan isi kolom <span class="font-mono">gambar</span>.
                            </p>
                        </div>
                        <p v-if="importError" class="text-red-500 text-xs">{{ importError }}</p>

                        <div v-if="importResult" class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700">
                            <p class="font-medium text-gray-800">
                                {{ importResult.total }} baris diproses —
                                dibuat {{ importResult.created }}, diperbarui {{ importResult.updated }}, gagal {{ importResult.failed }}.
                            </p>
                            <ul v-if="importResult.errors?.length" class="mt-2 max-h-40 overflow-y-auto list-disc pl-4 space-y-1 text-red-600">
                                <li v-for="(message, index) in importResult.errors" :key="index">{{ message }}</li>
                            </ul>
                        </div>

                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Tutup</button>
                            <button type="submit" :disabled="importProcessing || !importFile" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ importProcessing ? 'Mengunggah...' : 'Import' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
