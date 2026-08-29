@props([
    'href',
    'routePattern',
    'accent' => 'emerald',
])

@php
$accentStyles = match ($accent) {
    'ai' => [
        'active' => 'bg-emerald-50 text-emerald-700 font-bold',
        'inactive' => 'text-agri-muted hover:bg-emerald-50 hover:text-emerald-700',
        'bar' => 'bg-emerald-500',
    ],
    default => [
        'active' => 'bg-agri-green-soft text-agri-green font-bold',
        'inactive' => 'text-agri-muted hover:bg-agri-green-soft hover:text-agri-green',
        'bar' => 'bg-agri-green',
    ],
};

$isActive = request()->routeIs($routePattern);
@endphp

<a href="{{ $href }}"
   class="group relative flex items-center gap-3 overflow-hidden rounded-xl px-4 py-3 transition-all duration-300 {{ $isActive ? $accentStyles['active'] : $accentStyles['inactive'] }}">
    <div class="absolute left-0 h-8 w-1.5 rounded-r-full {{ $accentStyles['bar'] }} transition-transform duration-300 {{ $isActive ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>

    <span class="h-5 w-5 shrink-0 transition-transform duration-300 group-hover:scale-110" aria-hidden="true">
        {{ $icon }}
    </span>

    <span class="group-hover:translate-x-1 transition-transform duration-300">{{ $slot }}</span>
</a>
