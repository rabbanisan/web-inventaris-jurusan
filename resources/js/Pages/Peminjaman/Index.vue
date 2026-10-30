<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    peminjaman: Object,
    barangList: Array,
});

const showModal = ref(false);
const editMode = ref(false);
const showCreateModal = ref(false);
const createForm = useForm({
    barang_id: null,
    jumlah: 1,
    tanggal_pinjam: '',
    keterangan: '',
});
const form = useForm({
    id: null,
    status: '',
});

function openEdit(item) {
    form.id = item.id;
    form.status = item.status;
    editMode.value = true;
    showModal.value = true;
}

// Open create modal
function openCreate() {
    createForm.reset();
    showCreateModal.value = true;
}

// Submit new peminjaman
function submitCreate() {
    createForm.post('/peminjaman', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function submit() {
    if (editMode.value) {
        form.put(`/peminjaman/${form.id}`, {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}
</script>

<template>
    <Head title="Peminjaman" />
    <AppLayout>
        <template #header>Data Peminjaman</template>

        <div class="flex justify-end mb-4">
            <button @click="openCreate" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                + Ajukan Peminjaman
            </button>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">Barang</th>
                        <th class="px-4 py-2 text-left">Jumlah</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="item in peminjaman?.data" :key="item.id">
                        <td class="px-4 py-2">{{ item.id }}</td>
                        <td class="px-4 py-2">{{ item.user?.name }}</td>
                        <td class="px-4 py-2">{{ item.barang?.nama }}</td>
                        <td class="px-4 py-2">{{ item.jumlah }}</td>
                        <td class="px-4 py-2">{{ item.status }}</td>
                        <td class="px-4 py-2">
                            <button @click="openEdit(item)" class="text-blue-600 hover:text-blue-800">Ubah Status</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal untuk ubah status -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                    <h3 class="text-lg font-semibold mb-4">Ubah Status Peminjaman</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <select v-model="form.status" class="w-full border rounded p-2">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="returned">Returned</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-100 rounded">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Modal untuk tambah peminjaman -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showCreateModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
                    <h3 class="text-lg font-semibold mb-4">Ajukan Peminjaman</h3>
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                            <select v-model="createForm.barang_id" class="w-full border rounded p-2">
                                <option value="">-- Pilih Barang --</option>
                                <option v-for="b in barangList" :key="b.id" :value="b.id">
                                    {{ b.nama }} (Stok: {{ b.jumlah }})
                                </option>
                            </select>
                            <p v-if="createForm.errors.barang_id" class="text-red-500 text-xs mt-1">{{ createForm.errors.barang_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <input v-model="createForm.jumlah" type="number" min="1" class="w-full border rounded p-2" />
                            <p v-if="createForm.errors.jumlah" class="text-red-500 text-xs mt-1">{{ createForm.errors.jumlah }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pinjam</label>
                            <input v-model="createForm.tanggal_pinjam" type="date" class="w-full border rounded p-2" />
                            <p v-if="createForm.errors.tanggal_pinjam" class="text-red-500 text-xs mt-1">{{ createForm.errors.tanggal_pinjam }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea v-model="createForm.keterangan" rows="2" class="w-full border rounded p-2"></textarea>
                            <p v-if="createForm.errors.keterangan" class="text-red-500 text-xs mt-1">{{ createForm.errors.keterangan }}</p>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 rounded">Batal</button>
                            <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
