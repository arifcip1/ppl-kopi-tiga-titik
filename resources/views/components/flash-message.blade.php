{{-- 
    Komponen Flash Message & Validation Summary (PPL-24)
    Otomatis mendeteksi session flash (success, error, warning, info) dan error validasi backend ($errors)
--}}

<div {{ $attributes->merge(['class' => 'space-y-3 w-full']) }}>
    {{-- Pesan Sukses --}}
    @if (session('success'))
        <x-alert type="success" title="Berhasil" :message="session('success')" />
    @endif

    {{-- Pesan Error Global / Exception --}}
    @if (session('error'))
        <x-alert type="error" title="Perhatian" :message="session('error')" />
    @endif

    {{-- Pesan Peringatan --}}
    @if (session('warning'))
        <x-alert type="warning" title="Peringatan" :message="session('warning')" />
    @endif

    {{-- Pesan Informasi --}}
    @if (session('info'))
        <x-alert type="info" title="Informasi" :message="session('info')" />
    @endif

    {{-- Ringkasan Error Validasi Input ($errors) --}}
    @if (isset($errors) && $errors->any())
        <x-alert type="error" title="Terdapat Kesalahan Pengisian Formulir:">
            <ul class="list-disc pl-4 mt-1.5 space-y-1 font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
</div>

