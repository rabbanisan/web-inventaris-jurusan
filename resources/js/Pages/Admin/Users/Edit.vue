<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name ?? "",
    email: props.user.email ?? "",
    role: props.user.role ?? "user",
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);
const showConfirm = ref(false);

const roles = [
    {
        value: "admin",
        label: "Admin",
        badge: "Akses Penuh",
        desc: "Petugas Inventaris: mengelola barang, kategori, ruangan, peminjaman, dan user.",
    },
    {
        value: "user",
        label: "User",
        badge: "Akses Standar",
        desc: "Peminjam: melihat katalog dan mengajukan peminjaman barang.",
    },
];

const initials = computed(() =>
    (form.name || "?")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join(""),
);

function submit() {
    form.put(route("admin.users.update", props.user.id), {
        preserveScroll: true,
        onSuccess: () => form.reset("password", "password_confirmation"),
    });
}
</script>

<template>
    <Head title="Edit User" />

    <AppLayout>
        <template #header>Edit User</template>

        <div class="mx-auto max-w-2xl space-y-4">
            <!-- Ringkasan user -->
            <section
                class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-lg font-semibold text-white ring-2 ring-indigo-100"
                >
                    {{ initials }}
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-base font-semibold text-gray-900">
                        {{ user.name }}
                    </h2>
                    <p class="truncate text-sm text-gray-500">
                        {{ user.email }}
                    </p>
                    <span
                        class="mt-1 inline-block rounded border border-gray-200 bg-gray-50 px-2 py-0.5 font-mono text-xs text-gray-500"
                    >
                        #USR-{{ user.id }}
                    </span>
                </div>
            </section>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Informasi umum -->
                <section
                    class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-200 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-gray-800">
                            Informasi Umum
                        </h3>
                        <span class="text-xs text-gray-400">Wajib diisi</span>
                    </div>

                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Nama
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-400': form.errors.name }"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-400': form.errors.email }"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>
                </section>

                <!-- Role -->
                <section
                    class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-200 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-gray-800">
                            Role &amp; Hak Akses
                        </h3>
                    </div>

                    <div
                        role="radiogroup"
                        aria-label="Role user"
                        class="grid gap-2.5"
                    >
                        <label
                            v-for="r in roles"
                            :key="r.value"
                            class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-3.5 transition-colors"
                            :class="
                                form.role === r.value
                                    ? 'border-indigo-600 bg-indigo-50'
                                    : 'border-gray-200 bg-white hover:bg-gray-50'
                            "
                        >
                            <input
                                v-model="form.role"
                                type="radio"
                                name="role"
                                :value="r.value"
                                class="mt-1 text-indigo-600 focus:ring-indigo-500"
                            />
                            <div class="flex-1">
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{ r.label }}
                                    </span>
                                    <span
                                        class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide"
                                        :class="
                                            r.value === 'admin'
                                                ? 'border-purple-200 bg-purple-50 text-purple-700'
                                                : 'border-gray-200 bg-gray-100 text-gray-600'
                                        "
                                    >
                                        {{ r.badge }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ r.desc }}
                                </p>
                            </div>
                        </label>
                    </div>
                    <p v-if="form.errors.role" class="text-xs text-red-600">
                        {{ form.errors.role }}
                    </p>
                </section>

                <!-- Password -->
                <section
                    class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div class="border-b border-gray-200 pb-2">
                        <h3 class="text-sm font-semibold text-gray-800">
                            Ubah Password
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Kosongkan jika tidak ingin mengubah password
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Password baru (opsional)
                        </label>
                        <div class="relative mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-lg border-gray-300 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{
                                    'border-red-400': form.errors.password,
                                }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="
                                    showPassword
                                        ? 'Sembunyikan password'
                                        : 'Tampilkan password'
                                "
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            >
                                <svg
                                    v-if="!showPassword"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    />
                                </svg>
                            </button>
                        </div>
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-400">
                            Minimal 8 karakter
                        </p>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Konfirmasi password
                        </label>
                        <div class="relative mt-1">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-lg border-gray-300 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                :aria-label="
                                    showConfirm
                                        ? 'Sembunyikan konfirmasi'
                                        : 'Tampilkan konfirmasi'
                                "
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            >
                                <svg
                                    v-if="!showConfirm"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Aksi -->
                <div class="space-y-2 pb-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-11 w-full items-center justify-center rounded-xl bg-indigo-600 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Perubahan"
                        }}
                    </button>
                    <Link
                        :href="route('admin.users.index')"
                        class="flex h-11 w-full items-center justify-center rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
