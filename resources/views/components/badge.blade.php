@props(['variant' => 'default'])

@php
    $classes = [
        'default' => 'bg-agri-sage text-agri-muted border-agri-line',
        'success' => 'bg-agri-green-soft text-agri-green border-[#b8cfb9]',
        'warning' => 'bg-agri-amber-soft text-agri-amber border-[#f0d6b1]',
        'danger' => 'bg-agri-rose-soft text-agri-rose border-[#e7b9b5]',
    ][$variant] ?? 'bg-agri-sage text-agri-muted border-agri-line';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border {$classes}"]) }}>
    {{ $slot }}
</span>
