<x-app-layout>
    @php
        $items = method_exists($comparisonSets, 'getCollection')
            ? $comparisonSets->getCollection()
            : collect($comparisonSets);

        $isPaginator = method_exists($comparisonSets, 'total');
        $totalComparisons = $isPaginator ? $comparisonSets->total() : $items->count();
        $hasMultiplePages = method_exists($comparisonSets, 'hasPages') && $comparisonSets->hasPages();

$countScenarios = function ($item) {
            $snapshot = $item->scenarios_snapshot ?? null;

            // Jika masih berupa JSON string dari database, decode dulu
            if (is_string($snapshot)) {
                $snapshot = json_decode($snapshot, true);
            }

            if (is_array($snapshot)) {
                // Jika terbungkus key 'scenarios' di dalam JSON
                if (isset($snapshot['scenarios']) && is_array($snapshot['scenarios'])) {
                    return count($snapshot['scenarios']);
                }
                return count($snapshot);
            }

            // Fallback jika menggunakan relasi scenarios / scenarios_count
            if (isset($item->scenarios_count) && is_numeric($item->scenarios_count)) {
                return (int) $item->scenarios_count;
            }
            if (isset($item->scenarios) && is_countable($item->scenarios)) {
                return count($item->scenarios);
            }

            return 0;
        };

        $pageScenarioCount = $items->sum($countScenarios);

        $validEvidence = $items->pluck('evidence_coverage')->filter(fn ($val) => is_numeric($val));
        $avgEvidence = $validEvidence->isNotEmpty() ? round($validEvidence->avg(), 1) : null;

        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
    @endphp

    <div class="min-h-screen bg-gradient-to-b from-slate-50 via-cyan-50/25 to-emerald-50/20 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">

            {{-- ================= HEADER SECTION ================= --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10">
                {{-- Ambient Glows --}}
                <div class="pointer-events-none absolute -top-20 -right-16 h-64 w-64 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-emerald-500/20 blur-3xl"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-start gap-4 sm:gap-5">
                        {{-- Icon Container --}}
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-indigo-500 to-purple-600 p-0.5 shadow-lg shadow-indigo-500/30">
                            <div class="w-full h-full rounded-[14px] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center text-cyan-300">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/15 border border-cyan-400/30 text-cyan-300 text-[11px] font-bold uppercase tracking-widest mb-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Operational Digital Twin · Riwayat Perbandingan
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                                Comparison History
                            </h1>
                            <p class="text-sm text-slate-300 mt-1.5 max-w-2xl leading-relaxed">
                                Riwayat perbandingan simulasi untuk melihat dan meninjau keputusan pengiriman yang pernah dianalisis.
                            </p>
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('digital-twin.index') }}"
                           class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/15 text-white font-bold text-xs uppercase tracking-wider backdrop-blur-sm transition-all duration-200 hover:-translate-x-0.5">
                            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Digital Twin
                        </a>
                    </div>
                </div>
            </div>

            {{-- ================= SUMMARY STATISTIC CARDS ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                {{-- Card 1: Total Comparisons (Cyan) --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-50 via-white to-sky-50/70 border border-cyan-200/80 p-5 shadow-sm hover:shadow-md hover:shadow-cyan-500/10 transition-all duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">
                                Total Perbandingan
                            </p>
                            <p class="text-3xl font-black text-slate-900 mt-2">
                                {{ $totalComparisons }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 font-medium">
                                Riwayat simulasi tersimpan
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white flex items-center justify-center shadow-md shadow-cyan-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Total Scenarios (Purple / Indigo) --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-50 via-white to-purple-50/70 border border-indigo-200/80 p-5 shadow-sm hover:shadow-md hover:shadow-indigo-500/10 transition-all duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">
                                Total Skenario
                            </p>
                            <p class="text-3xl font-black text-slate-900 mt-2">
                                {{ $pageScenarioCount }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 font-medium">
                                {{ $hasMultiplePages ? 'Skenario pada halaman ini' : 'Skenario yang dibandingkan' }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Average Evidence Coverage (Emerald) --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-teal-50/70 border border-emerald-200/80 p-5 shadow-sm hover:shadow-md hover:shadow-emerald-500/10 transition-all duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                Rata-rata Data Pendukung
                            </p>
                            <p class="text-3xl font-black text-slate-900 mt-2">
                                {{ $avgEvidence !== null ? $avgEvidence . '%' : '—' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 font-medium">
                                {{ $hasMultiplePages ? 'Rata-rata pada halaman ini' : 'Kelengkapan data analisis' }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= HISTORY LIST SECTION ================= --}}
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 px-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-6 rounded-full bg-gradient-to-b from-emerald-500 to-cyan-500"></span>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                            Riwayat Analisis
                        </h2>
                    </div>

                    @if($items->isNotEmpty() && $isPaginator && $comparisonSets->firstItem())
                        <p class="text-xs font-semibold text-slate-500">
                            Menampilkan <span class="text-slate-800 font-bold">{{ $comparisonSets->firstItem() }}–{{ $comparisonSets->lastItem() }}</span> dari <span class="text-slate-800 font-bold">{{ $comparisonSets->total() }}</span> riwayat
                        </p>
                    @endif
                </div>

                @if($items->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($comparisonSets as $set)
                            @php
                                $scenarioCount = $countScenarios($set);

                                $commodity = $set->shipment?->harvest?->commodity ?? 'Tidak Diketahui';

                                // Preferred option formatting
                                $rawPreferred = $set->preferred_option;
                                if ($rawPreferred === 'current_plan') {
                                    $preferredLabel = 'Rencana Saat Ini';
                                    $preferredClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $preferredDot = 'bg-emerald-500';
                                } elseif (!empty($rawPreferred)) {
                                    $preferredLabel = \Illuminate\Support\Str::of((string) $rawPreferred)->replace(['_', '-'], ' ')->title();
                                    $preferredClasses = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                    $preferredDot = 'bg-indigo-500';
                                } else {
                                    $preferredLabel = 'Belum Ditentukan';
                                    $preferredClasses = 'bg-slate-100 text-slate-600 border-slate-200';
                                    $preferredDot = 'bg-slate-400';
                                }

                                // Evidence coverage visual state
                                $evidence = is_numeric($set->evidence_coverage) ? (float) $set->evidence_coverage : null;
                                $evidenceClamped = $evidence !== null ? max(0, min(100, $evidence)) : 0;

                                if ($evidence === null) {
                                    $evidenceTextClass = 'text-slate-500';
                                    $evidenceBarClass = 'bg-slate-300';
                                    $evidenceBgClass = 'bg-slate-100';
                                } elseif ($evidence >= 75) {
                                    $evidenceTextClass = 'text-emerald-700';
                                    $evidenceBarClass = 'bg-gradient-to-r from-emerald-500 to-teal-500';
                                    $evidenceBgClass = 'bg-emerald-100';
                                } elseif ($evidence >= 45) {
                                    $evidenceTextClass = 'text-amber-700';
                                    $evidenceBarClass = 'bg-gradient-to-r from-amber-400 to-orange-500';
                                    $evidenceBgClass = 'bg-amber-100';
                                } else {
                                    $evidenceTextClass = 'text-rose-700';
                                    $evidenceBarClass = 'bg-gradient-to-r from-rose-500 to-red-500';
                                    $evidenceBgClass = 'bg-rose-100';
                                }

                                // Indonesian readable date
                                $createdAtFormatted = $set->created_at
                                    ? $set->created_at->format('d') . ' ' . ($bulanIndo[(int) $set->created_at->format('n')] ?? $set->created_at->format('M')) . ' ' . $set->created_at->format('Y · H:i')
                                    : '—';
                            @endphp

                            <a href="{{ route('digital-twin.comparisons.show', $set) }}"
                               class="group block rounded-2xl bg-white border border-slate-200/90 hover:border-cyan-300 shadow-sm hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-200 hover:-translate-y-0.5 overflow-hidden">
                                <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">

                                    {{-- A. Comparison Identity (Col 4) --}}
                                    <div class="lg:col-span-4 flex items-start gap-3.5 min-w-0">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-50 to-indigo-100 border border-cyan-200/70 text-cyan-700 flex items-center justify-center group-hover:from-cyan-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-200 shadow-sm">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-cyan-700 transition-colors truncate">
                                                {{ $set->name }}
                                            </h3>
                                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-sky-700 text-[11px] font-bold">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                                    </svg>
                                                    Pengiriman #{{ $set->shipment_id }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-[11px] font-bold capitalize">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z" />
                                                    </svg>
                                                    {{ $commodity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- B. Scenario Count (Col 2) --}}
                                    <div class="lg:col-span-2 flex lg:flex-col items-center lg:items-start justify-between gap-2 bg-purple-50/60 lg:bg-transparent px-3.5 py-2.5 lg:p-0 rounded-xl border border-purple-100 lg:border-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                            Jumlah Skenario
                                        </span>
                                        <div class="inline-flex items-center gap-2 lg:mt-1.5 px-3 py-1 rounded-xl bg-purple-100/80 border border-purple-200/80 text-purple-800">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                            </svg>
                                            <span class="text-base font-black leading-none">{{ $scenarioCount }}</span>
                                            <span class="text-[11px] font-semibold text-purple-700">opsi</span>
                                        </div>
                                    </div>

                                    {{-- C. Preferred Decision (Col 2) --}}
                                    <div class="lg:col-span-2 flex lg:flex-col items-center lg:items-start justify-between gap-2">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                            Rekomendasi
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 lg:mt-1.5 px-3 py-1.5 rounded-xl border text-xs font-extrabold {{ $preferredClasses }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $preferredDot }}"></span>
                                            {{ $preferredLabel }}
                                        </span>
                                    </div>

                                    {{-- D. Evidence Coverage (Col 2) --}}
                                    <div class="lg:col-span-2">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                                Kelengkapan Data
                                            </span>
                                            <span class="text-xs font-black {{ $evidenceTextClass }}">
                                                {{ $evidence !== null ? $set->evidence_coverage . '%' : '—' }}
                                            </span>
                                        </div>
                                        <div class="w-full h-2.5 rounded-full {{ $evidenceBgClass }} overflow-hidden">
                                            <div class="h-full rounded-full {{ $evidenceBarClass }} transition-all duration-500"
                                                 style="width: {{ $evidenceClamped }}%"></div>
                                        </div>
                                    </div>

                                    {{-- E & F. Date + Open Detail Cue (Col 2) --}}
                                    <div class="lg:col-span-2 flex lg:flex-col items-center lg:items-end justify-between gap-2 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $createdAtFormatted }}</span>
                                        </div>

                                        <span class="inline-flex items-center gap-1 text-xs font-extrabold text-cyan-600 group-hover:text-cyan-700 transition-colors">
                                            Lihat Detail
                                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- ================= EMPTY STATE ================= --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-white via-cyan-50/50 to-indigo-50/60 border border-cyan-200/70 p-10 sm:p-16 text-center shadow-sm">
                        <div class="pointer-events-none absolute -top-16 -right-16 w-48 h-48 rounded-full bg-emerald-300/20 blur-2xl"></div>
                        <div class="pointer-events-none absolute -bottom-16 -left-16 w-48 h-48 rounded-full bg-indigo-300/20 blur-2xl"></div>

                        <div class="relative z-10 max-w-md mx-auto">
                            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-cyan-500 via-indigo-500 to-emerald-500 p-0.5 mx-auto mb-6 shadow-lg shadow-indigo-500/20">
                                <div class="w-full h-full rounded-[22px] bg-white flex items-center justify-center text-indigo-600">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>

                            <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                                Belum Ada Riwayat Analisis
                            </h3>
                            <p class="text-sm text-slate-600 mt-2.5 leading-relaxed">
                                Hasil perbandingan simulasi Digital Twin yang Anda simpan akan muncul di sini agar mudah ditinjau kembali kapan saja.
                            </p>

                            <div class="mt-7">
                                <a href="{{ route('digital-twin.index') }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white text-sm font-extrabold shadow-lg shadow-emerald-600/25 hover:-translate-y-0.5 transition-all duration-200">
                                    Mulai Analisis Digital Twin
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ================= PAGINATION ================= --}}
            @if(method_exists($comparisonSets, 'links'))
                <div class="pt-2">
                    {{ $comparisonSets->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>