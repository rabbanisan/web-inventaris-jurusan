<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useImport } from '@/Composables/useImport';

defineProps({ ruangan: Array });

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
    url: '/ruangan/import',
    reloadProps: ['ruangan'],
});

function openImport() {
    resetImport();
    showImportModal.value = true;
}

const form = useForm({
    id: null,
    nama: '',
    lokasi: '',
    keterangan: '',
});

function openCreate() {
    form.reset();
    editMode.value = false;
    showModal.value = true;
}

function openEdit(item) {
    form.id = item.id;
    form.nama = item.nama;
    form.lokasi = item.lokasi || '';
    form.keterangan = item.keterangan || '';
    editMode.value = true;
    showModal.value = true;
}

function submit() {
    if (editMode.value) {
        form.put(`/ruangan/${form.id}`, {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post('/ruangan', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus ruangan ini?')) {
        router.delete(`/ruangan/${id}`);
    }
}
</script>

<template>
    <Head title="Ruangan" />
    <AppLayout>
        <template #header>Data Ruangan</template>

        <div class="mb-4 flex justify-end space-x-2">
            <button @click="openImport" class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Import Excel
            </button>
            <button @click="openCreate" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                + Tambah Ruangan
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="!ruangan?.length">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data ruangan</td>
                        </tr>
                        <tr v-for="(item, index) in ruangan" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ item.nama }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.lokasi || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.keterangan || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.barang_count }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button @click="openEdit(item)" class="text-blue-600 hover:text-blue-800">Edit</button>
                                <button @click="destroy(item.id)" class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        {{ editMode ? 'Edit Ruangan' : 'Tambah Ruangan' }}
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan</label>
                            <input v-model="form.nama" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input v-model="form.lokasi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea v-model="form.keterangan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
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
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Import Ruangan dari Excel</h3>
                    <p class="text-xs text-gray-500 mb-2">
                        Kolom wajib: <span class="font-mono">nama</span>.
                        Kolom opsional: <span class="font-mono">lokasi, keterangan</span>.
                        Baris dengan nama yang sudah ada akan diperbarui.
                    </p>
                    <a href="/ruangan/template" class="inline-block text-sm text-blue-600 hover:text-blue-800 mb-3">
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
