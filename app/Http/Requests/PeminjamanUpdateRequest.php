<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PeminjamanUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'approved', 'returned', 'rejected'])],
            'tanggal_kembali' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Jika status di‑set ke "returned" dan tanggal_kembali belum diisi,
     * otomatis isi dengan tanggal hari ini.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('status') === 'returned' && !$this->filled('tanggal_kembali')) {
            $this->merge(['tanggal_kembali' => now()->toDateString()]);
        }
    }

    /**
     * Validasi tambahan untuk transisi status dan cek stok saat approve.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $peminjaman = $this->route('peminjaman');

            if (! $peminjaman) {
                return;
            }

            $old = $peminjaman->status;
            $new = $this->input('status');

            // Tidak boleh langsung dari pending ke returned
            if ($old === 'pending' && $new === 'returned') {
                $validator->errors()->add('status', 'Tidak dapat langsung mengembalikan peminjaman yang masih pending.');
            }

            // Saat approve, pastikan stok masih cukup
            if ($old !== 'approved' && $new === 'approved') {
                if ($peminjaman->barang->jumlah < $peminjaman->jumlah) {
                    $validator->errors()->add('status', 'Stok barang tidak mencukupi untuk meng‑approve peminjaman ini.');
                }
            }
        });
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status peminjaman wajib dipilih.',
            'status.in' => 'Status peminjaman tidak valid.',
            'tanggal_kembali.date' => 'Tanggal kembali tidak valid.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ];
    }

    /**
     * Nama atribut untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'status peminjaman',
            'tanggal_kembali' => 'tanggal kembali',
            'keterangan' => 'keterangan',
        ];
    }
}
