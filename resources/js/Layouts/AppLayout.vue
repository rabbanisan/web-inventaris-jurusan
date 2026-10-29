<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const sidebarOpen = ref(true);

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const isAdmin = computed(() => user.value?.role === "admin");

const navigation = computed(() => [
    { name: "Dashboard", href: "/dashboard", icon: "dashboard" },
    ...(isAdmin.value
        ? [
              { name: "Barang", href: "/barang", icon: "inventory" },
              { name: "Kategori", href: "/kategori", icon: "category" },
              { name: "Ruangan", href: "/ruangan", icon: "room" },
          ]
        : []),
]);

function isActive(href) {
    return page.url.startsWith(href);
}

function logout() {
    router.post(route("logout"));
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-16'"
            class="fixed inset-y-0 left-0 z-30 bg-white border-r border-gray-200 transition-all duration-300"
        >
            <div class="flex items-center h-16 px-4 border-b border-gray-200">
                <h1
                    v-if="sidebarOpen"
                    class="text-lg font-bold text-gray-800 truncate"
                >
                    Inventaris Jurusan
                </h1>
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="ml-auto p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>
            <nav class="mt-4 px-2 space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        isActive(item.href)
                            ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                        'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    ]"
                >
                    <span
                        class="w-5 h-5 flex-shrink-0 flex items-center justify-center"
                    >
                        <svg
                            v-if="item.icon === 'dashboard'"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"
                            />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'inventory'"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'category'"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                            />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'room'"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                    </span>
                    <span v-if="sidebarOpen" class="ml-3">{{ item.name }}</span>
                </Link>
            </nav>
        </aside>

        <!-- Main content -->
        <div
            :class="sidebarOpen ? 'ml-64' : 'ml-16'"
            class="transition-all duration-300"
        >
            <header
                class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6"
            >
                <h2 class="text-lg font-semibold text-gray-800">
                    <slot name="header" />
                </h2>
                <div class="flex items-center space-x-3">
                    <div class="text-right leading-tight">
                        <div class="text-sm font-medium text-gray-800">
                            {{ user?.name ?? "Tamu" }}
                        </div>
                        <div class="text-xs text-gray-500 capitalize">
                            {{ user?.role ?? "-" }}
                        </div>
                    </div>
                    <button
                        v-if="user"
                        @click="logout"
                        class="px-3 py-1.5 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                    >
                        Logout
                    </button>
                </div>
            </header>
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
