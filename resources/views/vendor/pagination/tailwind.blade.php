@if ($paginator->hasPages())

<nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center">

    <div class="flex items-center gap-2">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())

            <span
                class="px-4 py-2 rounded-xl
                bg-slate-800
                text-slate-500
                text-sm
                font-bold">
                ←
            </span>

        @else

            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-4 py-2 rounded-xl
                bg-slate-900
                border border-white/10
                text-white
                hover:bg-blue-600
                transition">
                ←
            </a>

        @endif



        {{-- Numbers --}}
        @foreach ($elements as $element)

            @if (is_string($element))

                <span
                class="px-4 py-2 rounded-xl
                bg-slate-900
                text-slate-400">
                    {{ $element }}
                </span>

            @endif



            @if (is_array($element))

                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())

                        <span
                        class="px-4 py-2 rounded-xl
                        bg-blue-500
                        text-white
                        font-black
                        shadow-lg
                        shadow-blue-500/30">
                            {{ $page }}
                        </span>

                    @else

                        <a href="{{ $url }}"
                        class="px-4 py-2 rounded-xl
                        bg-slate-900
                        border border-white/10
                        text-slate-300
                        hover:border-blue-400
                        hover:text-white
                        transition">
                            {{ $page }}
                        </a>

                    @endif

                @endforeach

            @endif

        @endforeach



        {{-- Next --}}
        @if ($paginator->hasMorePages())

            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-4 py-2 rounded-xl
                bg-slate-900
                border border-white/10
                text-white
                hover:bg-blue-600
                transition">
                →
            </a>

        @else

            <span
                class="px-4 py-2 rounded-xl
                bg-slate-800
                text-slate-500">
                →
            </span>

        @endif

    </div>

</nav>

@endif