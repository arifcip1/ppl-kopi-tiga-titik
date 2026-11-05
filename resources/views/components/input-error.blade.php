@props(['messages', 'for' => null])

@php
    // Jika parameter 'for' diberikan, ambil error langsung dari $errors
    if ($for && empty($messages) && isset($errors) && $errors->has($for)) {
        $messages = $errors->get($for);
    }
@endphp

@if (!empty($messages))
    <ul {{ $attributes->merge(['class' => 'text-[11px] text-rose-600 font-semibold mt-1 space-y-0.5 animate-fadeIn']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm shrink-0">error</span>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif

