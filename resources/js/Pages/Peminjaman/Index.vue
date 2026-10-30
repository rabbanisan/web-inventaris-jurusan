<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    peminjaman: Object,
});

const showModal = ref(false);
const editMode = ref(false);

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
    </AppLayout>
</template>
