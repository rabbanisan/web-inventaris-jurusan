<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Barang;
use Illuminate\Validation\Validator;

class PeminjamanStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'barang_id'      => ['required', 'exists:barangs,id'],
            'jumlah'         => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Validasi tambahan: pastikan stok barang mencukupi.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $barang = Barang::find($this->input('barang_id'));

            if (! $barang) {
                return;
            }

            if ((int) $this->input('jumlah') > $barang->jumlah) {
                $validator->errors()->add('jumlah', 'Stok tidak mencukupi. Sisa stok: '.$barang->jumlah);
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
            'barang_id.required' => 'Barang wajib dipilih.',
            'barang_id.exists' => 'Barang yang dipilih tidak ditemukan.',
            'jumlah.required' => 'Jumlah pinjam wajib diisi.',
            'jumlah.integer' => 'Jumlah pinjam harus berupa angka.',
            'jumlah.min' => 'Jumlah pinjam minimal 1.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Tanggal pinjam tidak valid.',
            'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh sebelum hari ini.',
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
            'barang_id' => 'barang',
            'jumlah' => 'jumlah pinjam',
            'tanggal_pinjam' => 'tanggal pinjam',
            'keterangan' => 'keterangan',
        ];
    }
}
