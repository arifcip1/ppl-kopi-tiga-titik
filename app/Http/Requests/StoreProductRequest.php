<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class StoreProductRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     * Hanya admin/owner yang berhak menambah produk (BR-24).
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'owner']);
    }

    /**
     * Aturan validasi penyimpanan produk baru (BR-01, FR-2.2, FR-2.3).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'      => ['required', 'string', 'max:255', 'unique:products,nama'],
            'rasa'      => ['nullable', 'string', 'max:255'],
            'harga'     => ['required', 'integer', 'min:1'],
            'stok'      => ['required', 'integer', 'min:0'],
            'komposisi' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'keawetan'  => ['required', 'string', 'max:255'],
            'foto'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'aktif'     => ['nullable'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia (BR-23).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required'      => 'Nama varian produk wajib diisi.',
            'nama.string'        => 'Nama varian produk harus berupa teks.',
            'nama.max'           => 'Nama varian produk maksimal 255 karakter.',
            'nama.unique'        => 'Nama varian produk sudah terdaftar di sistem.',
            'harga.required'     => 'Harga jual wajib diisi.',
            'harga.integer'      => 'Harga jual harus berupa bilangan bulat.',
            'harga.min'          => 'Harga jual harus berupa bilangan bulat lebih dari 0.',
            'stok.required'      => 'Stok harian wajib diisi.',
            'stok.integer'       => 'Stok harian harus berupa angka bulat.',
            'stok.min'           => 'Stok produk tidak boleh bernilai negatif.',
            'komposisi.required' => 'Daftar komposisi bahan wajib diisi.',
            'deskripsi.required' => 'Deskripsi singkat profil rasa wajib diisi.',
            'keawetan.required'  => 'Masa keawetan dan penyimpanan wajib diisi.',
            'foto.image'         => 'File foto harus berupa gambar (JPG, PNG, atau WebP).',
            'foto.mimes'         => 'Format foto harus berupa JPG, PNG, atau WebP.',
            'foto.max'           => 'Ukuran foto botol maksimal 2 MB.',
        ];
    }

    /**
     * Nama atribut untuk pesan validasi default.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama'      => 'nama produk',
            'rasa'      => 'profil rasa',
            'harga'     => 'harga jual',
            'stok'      => 'stok produk',
            'komposisi' => 'komposisi bahan',
            'deskripsi' => 'deskripsi produk',
            'keawetan'  => 'masa keawetan',
            'foto'      => 'foto produk',
        ];
    }

    /**
     * Format kegagalan validasi jika request berupa JSON/AJAX sesuai standar Kontrak Bab 13.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Data produk yang dimasukkan tidak valid.',
                'errors'  => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}

