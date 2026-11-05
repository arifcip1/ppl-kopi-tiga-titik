@props([
    'type' => 'info', // 'success', 'error', 'warning', 'info'
    'title' => null,
    'message' => null,
    'dismissible' => true
])

@php
    $typeClasses = [
        'success' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'text' => 'text-emerald-900',
            'icon' => 'check_circle',
            'iconColor' => 'text-emerald-600',
            'closeColor' => 'text-emerald-500 hover:text-emerald-800'
        ],
        'error' => [
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-200',
            'text' => 'text-rose-900',
            'icon' => 'error',
            'iconColor' => 'text-rose-600',
            'closeColor' => 'text-rose-500 hover:text-rose-800'
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'text' => 'text-amber-900',
            'icon' => 'warning',
            'iconColor' => 'text-amber-600',
            'closeColor' => 'text-amber-500 hover:text-amber-800'
        ],
        'info' => [
            'bg' => 'bg-sky-50',
            'border' => 'border-sky-200',
            'text' => 'text-sky-900',
            'icon' => 'info',
            'iconColor' => 'text-sky-600',
            'closeColor' => 'text-sky-500 hover:text-sky-800'
        ],
    ];

    $style = $typeClasses[$type] ?? $typeClasses['info'];
@endphp

<div {{ $attributes->merge(['class' => "p-3.5 sm:p-4 rounded-2xl border {$style['bg']} {$style['border']} {$style['text']} shadow-xs flex items-start gap-3 transition-all duration-300 relative group"]) }} role="alert">
    <!-- Icon -->
    <span class="material-symbols-outlined {{ $style['iconColor'] }} text-xl shrink-0 mt-0.5 select-none">
        {{ $style['icon'] }}
    </span>

    <!-- Content -->
    <div class="flex-1 text-xs leading-relaxed">
        @if ($title)
            <h4 class="font-bold font-['Plus_Jakarta_Sans'] text-xs sm:text-sm mb-0.5">
                {{ $title }}
            </h4>
        @endif

        @if ($message)
            <p class="font-medium">{{ $message }}</p>
        @endif

        {{ $slot }}
    </div>

    <!-- Dismiss Button -->
    @if ($dismissible)
        <button type="button" onclick="this.closest('[role=alert]').remove()" class="p-1 -mr-1 -mt-1 rounded-lg {{ $style['closeColor'] }} hover:bg-black/5 transition-colors cursor-pointer" title="Tutup Notifikasi">
            <span class="material-symbols-outlined text-base block">close</span>
        </button>
    @endif
</div>

