@props(['variant' => 'default'])

@php
    $dotClasses = [
        'default' => 'bg-agri-muted',
        'success' => 'bg-agri-green',
        'warning' => 'bg-agri-amber',
        'danger' => 'bg-agri-rose',
    ][$variant] ?? 'bg-agri-muted';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <span class="w-2 h-2 rounded-full {{ $dotClasses }}" aria-hidden="true"></span>
    <span class="text-sm font-bold text-agri-ink">{{ $slot }}</span>
</div>
