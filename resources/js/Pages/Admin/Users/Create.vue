<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

function submit() {
    form.post(route('admin.users.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add New User
            </h2>
        </template>

        <div class="py-6 max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block font-medium">Name</label>
                    <input v-model="form.name" type="text" class="input" />
                    <div v-if="form.errors.name" class="text-red-600 text-sm">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="block font-medium">Email</label>
                    <input v-model="form.email" type="email" class="input" />
                    <div v-if="form.errors.email" class="text-red-600 text-sm">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label class="block font-medium">Password</label>
                    <input v-model="form.password" type="password" class="input" />
                    <div v-if="form.errors.password" class="text-red-600 text-sm">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div>
                    <label class="block font-medium">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" class="input" />
                </div>

                <div>
                    <label class="block font-medium">Role</label>
                    <select v-model="form.role" class="input">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">
                    Save
                </button>
            </form>
        </div>
    </AppLayout>
</template>
