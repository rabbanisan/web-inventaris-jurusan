<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ kategori: Array });

const showModal = ref(false);
const editMode = ref(false);

const form = useForm({
    id: null,
    nama: '',
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
    form.keterangan = item.keterangan || '';
    editMode.value = true;
    showModal.value = true;
}

function submit() {
    if (editMode.value) {
        form.put(`/kategori/${form.id}`, {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post('/kategori', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus kategori ini?')) {
        router.delete(`/kategori/${id}`);
    }
}
</script>

<template>
    <Head title="Kategori" />
    <AppLayout>
        <template #header>Data Kategori</template>

        <div class="mb-4 flex justify-end">
            <button @click="openCreate" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                + Tambah Kategori
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="!kategori?.length">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data kategori</td>
                        </tr>
                        <tr v-for="(item, index) in kategori" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ item.nama }}</td>
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
                        {{ editMode ? 'Edit Kategori' : 'Tambah Kategori' }}
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                            <input v-model="form.nama" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
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
    </AppLayout>
</template>
