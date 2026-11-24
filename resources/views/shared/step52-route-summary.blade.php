@php
    $routeDecision =
        $routeDecision
        ?? $decisionAnalysis['route_decision']
        ?? null;

    $isStorageRoute =
        $isStorageStability
        ?? (
            (
                $routeDecision['analysis']['commodity_profile']['quality_model_type']
                ?? null
            ) === 'storage_stability'
            || !empty(
                $routeDecision['analysis']['quality_prediction']['storage_stability_reference_available']
                ?? false
            )
        );
@endphp

@if($routeDecision)
    @php
        $routeScore =
            $routeDecision['route_score']
            ?? null;

        $feasibility =
            $routeDecision[
                'freshness_feasibility'
            ] ?? 'Unavailable';

        $delayTolerance =
            $routeDecision[
                'delay_tolerance_hours'
            ] ?? null;

        $transitMargin =
            $routeDecision[
                'transit_margin_hours'
            ] ?? null;

        $quality =
            $routeDecision[
                'projected_arrival_quality'
            ] ?? null;

        $risk =
            $routeDecision[
                'projected_risk_score'
            ] ?? null;

        $reason =
            $routeDecision[
                'recommendation_reason'
            ] ?? null;

        $routeDecisionLabel =
            $isStorageRoute
                ? 'Keputusan Rute Berbasis Penyimpanan'
                : 'Keputusan Rute Berbasis Kesegaran';

        $routeDecisionDescription =
            $isStorageRoute
                ? 'Menilai apakah rute yang direncanakan tetap sesuai dengan batas waktu penyimpanan operasional pengiriman.'
                : 'Menilai apakah rute yang direncanakan mampu menjaga batas waktu kesegaran operasional pengiriman.';

        $safeRouteAction =
            $isStorageRoute
                ? 'Pertahankan rute saat ini dan pantau batas kondisi penyimpanan.'
                : 'Pertahankan rute saat ini dan pantau batas kesegaran produk.';

        $displayReason = $reason;

        if ($isStorageRoute && $displayReason) {
            $displayReason = str_ireplace(
                [
                    'freshness feasibility',
                    'freshness-safe route',
                    'operational freshness window',
                    'freshness window',
                    'kelayakan kesegaran',
                    'batas kesegaran',
                ],
                [
                    'kelayakan kondisi penyimpanan',
                    'rute sesuai standar penyimpanan',
                    'batas waktu penyimpanan operasional',
                    'batas kondisi penyimpanan',
                    'kelayakan kondisi penyimpanan',
                    'batas kondisi penyimpanan',
                ],
                $displayReason
            );
        }
    @endphp

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 pb-5 border-b border-slate-800">
            <div>
                <p class="text-xs uppercase font-extrabold tracking-wider text-teal-300">
                    {{ $routeDecisionLabel }}
                </p>
                <h3 class="text-xl sm:text-2xl font-black text-white mt-1.5">
                    Penilaian Rute Saat Ini
                </h3>
                <p class="text-sm text-slate-300 mt-2 max-w-xl leading-relaxed">
                    {{ $routeDecisionDescription }}
                </p>
            </div>

            <span class="inline-flex items-center gap-2 self-start px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider shrink-0
                {{ $feasibility === 'Safe' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/35' : '' }}
                {{ $feasibility === 'Tight' ? 'bg-amber-500/15 text-amber-300 border border-amber-400/35' : '' }}
                {{ $feasibility === 'Breach' ? 'bg-rose-500/15 text-rose-300 border border-rose-400/35' : '' }}
                {{ !in_array($feasibility, ['Safe', 'Tight', 'Breach'], true) ? 'bg-slate-800 text-slate-300 border border-slate-700' : '' }}
            ">
                <span class="w-2 h-2 rounded-full bg-current"></span>
                @if($feasibility === 'Safe')
                    Aman
                @elseif($feasibility === 'Tight')
                    Waktu Terbatas
                @elseif($feasibility === 'Breach')
                    Melewati Batas
                @else
                    Tidak Tersedia
                @endif
            </span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            {{-- Skor Rute --}}
            <div class="bg-slate-800/75 rounded-2xl border border-blue-500/25 p-4 sm:p-5">
                <p class="text-xs font-bold text-blue-300">
                    Skor Rute
                </p>
                <p class="text-2xl sm:text-3xl font-black text-white mt-2">
                    {{ $routeScore !== null ? $routeScore : '—' }}
                    <span class="text-sm font-bold text-slate-400">/100</span>
                </p>
            </div>

            {{-- Kualitas Saat Tiba --}}
            <div class="bg-slate-800/75 rounded-2xl border border-emerald-500/25 p-4 sm:p-5">
                <p class="text-xs font-bold text-emerald-300">
                    Kualitas Saat Tiba
                </p>
                @if($quality !== null)
                    <p class="text-2xl sm:text-3xl font-black text-white mt-2">
                        {{ $quality }}
                        <span class="text-sm font-bold text-slate-400">/100</span>
                    </p>
                @else
                    <p class="text-base font-black text-slate-200 mt-2">
                        Tidak diestimasi
                    </p>
                    <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                        Belum ada acuan kurva kesegaran produk segar untuk model komoditas ini.
                    </p>
                @endif
            </div>

            {{-- Risiko Operasional --}}
            <div class="bg-slate-800/75 rounded-2xl border border-rose-500/25 p-4 sm:p-5">
                <p class="text-xs font-bold text-rose-300">
                    Risiko Operasional
                </p>
                <p class="text-2xl sm:text-3xl font-black mt-2
                    {{ $risk === null ? 'text-slate-300' : ($risk >= 60 ? 'text-rose-400' : ($risk >= 30 ? 'text-amber-400' : 'text-emerald-400')) }}">
                    {{ $risk !== null ? $risk : '—' }}
                    <span class="text-sm font-bold text-slate-400">/100</span>
                </p>
            </div>

            {{-- Toleransi Keterlambatan --}}
            <div class="bg-slate-800/75 rounded-2xl border border-amber-500/25 p-4 sm:p-5">
                <p class="text-xs font-bold text-amber-300">
                    Toleransi Keterlambatan
                </p>
                <p class="text-2xl sm:text-3xl font-black text-white mt-2">
                    {{ $delayTolerance !== null ? number_format($delayTolerance, 1, ',', '.') : '—' }}
                    <span class="text-sm font-bold text-slate-400">jam</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            {{-- Waktu Cadangan Perjalanan --}}
            <div class="bg-slate-950/50 rounded-2xl border border-slate-800 p-4 sm:p-5">
                <p class="text-xs font-bold text-sky-300">
                    Waktu Cadangan Perjalanan
                </p>
                <p class="text-xl font-black text-white mt-1.5">
                    {{ $transitMargin !== null ? number_format($transitMargin, 1, ',', '.') . ' jam' : 'Tidak tersedia' }}
                </p>
            </div>

            {{-- Tindakan Rute --}}
            <div class="bg-slate-950/50 rounded-2xl border border-slate-800 p-4 sm:p-5">
                <p class="text-xs font-bold text-slate-300">
                    Tindakan Rute
                </p>
                <p class="text-sm sm:text-base font-bold mt-1.5 leading-relaxed
                    {{ $feasibility === 'Safe' ? 'text-emerald-300' : '' }}
                    {{ $feasibility === 'Tight' ? 'text-amber-300' : '' }}
                    {{ $feasibility === 'Breach' ? 'text-rose-300' : '' }}
                    {{ !in_array($feasibility, ['Safe', 'Tight', 'Breach'], true) ? 'text-slate-300' : '' }}
                ">
                    @if($feasibility === 'Safe')
                        {{ $safeRouteAction }}
                    @elseif($feasibility === 'Tight')
                        Utamakan keberangkatan dan hindari penundaan tambahan.
                    @elseif($feasibility === 'Breach')
                        Perubahan rute saja tidak cukup; diperlukan penanganan operasional segera.
                    @else
                        Penilaian rute belum tersedia.
                    @endif
                </p>
            </div>
        </div>

        @if($displayReason)
            <p class="text-sm text-slate-300 leading-relaxed mt-5 pt-5 border-t border-slate-800">
                {{ $displayReason }}
            </p>
        @endif
    </div>
@endif