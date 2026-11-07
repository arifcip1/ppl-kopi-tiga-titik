<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddToCartRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     * Hanya pelanggan terautentikasi yang berhak mengelola keranjang (BR-24).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi tambah item ke keranjang (BR-04, FR-4.4).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'jumlah'     => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.integer'  => 'ID produk tidak valid.',
            'product_id.exists'   => 'Produk yang dipilih tidak ditemukan dalam sistem.',
            'jumlah.integer'      => 'Jumlah botol harus berupa angka bilangan bulat.',
            'jumlah.min'          => 'Jumlah pesanan minimal 1 botol.',
        ];
    }

    /**
     * Format kegagalan validasi jika request berupa JSON/AJAX sesuai standar Kontrak Bab 13.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*') || $this->ajax()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Data keranjang tidak valid.',
                'errors'  => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}

