import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

function csrfToken() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

export function useImport({ url, reloadProps = [] }) {
    const file = ref(null);
    const zip = ref(null);
    const processing = ref(false);
    const result = ref(null);
    const error = ref(null);

    function reset() {
        file.value = null;
        zip.value = null;
        processing.value = false;
        result.value = null;
        error.value = null;
    }

    function onFileChange(event) {
        file.value = event.target.files?.[0] ?? null;
        result.value = null;
        error.value = null;
    }

    function onZipChange(event) {
        zip.value = event.target.files?.[0] ?? null;
        result.value = null;
        error.value = null;
    }

    function reload() {
        if (reloadProps.length) {
            router.reload({ only: reloadProps });
        } else {
            router.reload();
        }
    }

    async function submit() {
        if (!file.value || processing.value) {
            return;
        }

        processing.value = true;
        result.value = null;
        error.value = null;

        const data = new FormData();
        data.append('file', file.value);

        if (zip.value) {
            data.append('images', zip.value);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken(),
                },
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                error.value =
                    payload?.errors?.file?.[0] ??
                    payload?.errors?.images?.[0] ??
                    payload?.message ??
                    'Gagal mengunggah file.';

                return;
            }

            result.value = payload;

            if ((payload.created ?? 0) + (payload.updated ?? 0) > 0) {
                reload();
            }
        } catch (e) {
            error.value = 'Terjadi kesalahan saat mengunggah file.';
        } finally {
            processing.value = false;
        }
    }

    return { file, zip, processing, result, error, reset, onFileChange, onZipChange, submit };
}
