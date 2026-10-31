<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Tampilkan halaman kelola produk beserta statistik inventaris real-time.
     * Sesuai F2 & FR-2.1
     */
    public function index()
    {
        $products = Product::orderBy('id', 'asc')->get();

        // Hitung metrik statistik inventaris
        $totalMenu = $products->count();
        $totalStok = $products->sum('stok');
        // BR-10 & BR-19: Ambang peringatan restok adalah stok <= 5
        $perluRestok = $products->where('stok', '<=', 5)->count();
        $katalogAktif = $products->where('aktif', true)->count();

        return view('Kelola Produk Page', compact(
            'products',
            'totalMenu',
            'totalStok',
            'perluRestok',
            'katalogAktif'
        ));
    }

    /**
     * Simpan varian produk baru ke database dan tangani unggah foto.
     * Sesuai FR-2.2, FR-2.3 (unggah foto max 2MB), dan BR-01 (harga > 0, stok >= 0).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:products,nama'],
            'rasa' => ['nullable', 'string', 'max:255'],
            'harga' => ['required', 'integer', 'min:1'],
            'stok' => ['required', 'integer', 'min:0'],
            'komposisi' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'keawetan' => ['required', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'aktif' => ['nullable'],
        ], [
            'nama.required' => 'Nama varian produk wajib diisi.',
            'nama.unique' => 'Nama varian produk sudah terdaftar di sistem.',
            'harga.min' => 'Harga jual harus berupa bilangan bulat lebih dari 0.',
            'stok.min' => 'Stok produk tidak boleh bernilai negatif.',
            'foto.max' => 'Ukuran foto botol maksimal 2 MB.',
            'foto.image' => 'File foto harus berupa gambar (JPG, PNG, atau WebP).',
        ]);

        // Tangani unggah foto ke storage publik (FR-2.3: storage:link)
        if ($request->hasFile('foto')) {
            $validated['foto_path'] = $request->file('foto')->store('products', 'public');
        }

        // Default aktif = true jika checkbox dicentang, atau jika tidak diset
        $validated['aktif'] = $request->has('aktif') ? true : false;

        $product = Product::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk baru ' . $product->nama . ' berhasil ditambahkan!',
                'data' => $product,
            ], 201);
        }

        return redirect()->route('owner.produk.index')
            ->with('success', 'Produk baru ' . $product->nama . ' berhasil ditambahkan!');
    }

    /**
     * Perbarui data produk, harga, stok, dan ganti foto.
     * Sesuai FR-2.3 (ganti foto hapus file lama) & FR-2.4 (ubah stok).
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('products', 'nama')->ignore($product->id)],
            'rasa' => ['nullable', 'string', 'max:255'],
            'harga' => ['required', 'integer', 'min:1'],
            'stok' => ['required', 'integer', 'min:0'],
            'komposisi' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'keawetan' => ['required', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'aktif' => ['nullable'],
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'nama.unique' => 'Nama produk sudah digunakan oleh produk lain.',
            'harga.min' => 'Harga jual harus lebih dari Rp 0.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'foto.max' => 'Ukuran file foto maksimal 2 MB.',
        ]);

        // Jika ada foto baru diunggah, hapus foto lama dari storage publik (FR-2.3)
        if ($request->hasFile('foto')) {
            if ($product->foto_path && Storage::disk('public')->exists($product->foto_path)) {
                Storage::disk('public')->delete($product->foto_path);
            }
            $validated['foto_path'] = $request->file('foto')->store('products', 'public');
        }

        $validated['aktif'] = $request->has('aktif') ? true : false;

        $product->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perubahan produk ' . $product->nama . ' berhasil disimpan!',
                'data' => $product,
            ]);
        }

        return redirect()->route('owner.produk.index')
            ->with('success', 'Perubahan produk ' . $product->nama . ' berhasil disimpan!');
    }

    /**
     * Nonaktifkan produk dari katalog (Soft Deactivate sesuai BR-27 & FR-2.5).
     * Produk yang pernah dipesan tidak dihapus fisik agar relasi riwayat tetap aman.
     */
    public function destroy(Product $product)
    {
        $product->update(['aktif' => false]);

        $message = 'Menu ' . $product->nama . ' berhasil dinonaktifkan dari katalog!';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $product,
            ]);
        }

        return redirect()->route('owner.produk.index')->with('success', $message);
    }

    /**
     * Toggle status aktif/nonaktif produk secara langsung.
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['aktif' => !$product->aktif]);

        $statusText = $product->aktif ? 'diaktifkan kembali' : 'dinonaktifkan';
        $message = 'Menu ' . $product->nama . ' berhasil ' . $statusText . '!';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $product,
            ]);
        }

        return redirect()->route('owner.produk.index')->with('success', $message);
    }

    /**
     * Perbarui jumlah stok produk secara cepat (FR-2.4).
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stok' => ['required', 'integer', 'min:0'],
        ], [
            'stok.min' => 'Stok produk tidak boleh bernilai negatif.',
        ]);

        $product->update(['stok' => $validated['stok']]);

        $message = 'Stok ' . $product->nama . ' berhasil diperbarui menjadi ' . $product->stok . ' botol!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $product,
            ]);
        }

        return redirect()->route('owner.produk.index')->with('success', $message);
    }
}

