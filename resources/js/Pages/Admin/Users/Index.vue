<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    users: Object,
});

function destroy(id) {
    if (!confirm("Delete this user?")) return;

    useForm().delete(route("admin.users.destroy", id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola User
            </h2>
        </template>

        <div class="py-6">
            <Link
                :href="route('admin.users.create')"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Add New User
            </Link>

            <table class="min-w-full bg-white border mt-4">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">{{ user.name }}</td>
                        <td class="px-4 py-2">{{ user.email }}</td>
                        <td class="px-4 py-2 capitalize">{{ user.role }}</td>
                        <td class="px-4 py-2">
                            <Link
                                :href="route('admin.users.edit', user.id)"
                                class="text-blue-600 mr-2"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy(user.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <Pagination :links="users.links" class="mt-4" />
        </div>
    </AppLayout>
</template>
