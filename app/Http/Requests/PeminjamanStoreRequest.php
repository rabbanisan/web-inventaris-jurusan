<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
