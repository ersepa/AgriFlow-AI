<x-app-layout>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 6px 24px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
        }

        .btn-animate {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(5, 150, 105, 0.25);
        }
        .btn-animate:active {
            transform: translateY(0) scale(0.98);
        }

        .agri-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.07);
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen bg-[#F8FAFC] bg-grid-pattern pb-16 animate-card">
        <div class="max-w-7xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ================= HEADER ANALISIS AI ================= --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide mb-3 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>AgriFlow Decision Engine</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Analisis AI</h1>
                    <p class="text-slate-600 mt-1.5 font-medium text-sm sm:text-base max-w-2xl">
                        Pusat penilaian kecerdasan buatan untuk memahami risiko pengiriman, kondisi produk saat tiba, dan rekomendasi tindakan yang praktis.
                    </p>
                </div>

                <a href="{{ route('ai-analysis.history') }}"
                   class="btn-animate group inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-2xl bg-slate-900 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-sm transition-all duration-300 shrink-0">
                    <svg class="w-4 h-4 transition-transform group-hover:-rotate-45 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Lihat Riwayat Analisis</span>
                </a>
            </div>

            {{-- ================= HASIL ANALISIS AI (JIKA TERSEDIA) ================= --}}
            @if(session('ai_result'))
                @php
                    $riskLevel = session('risk_level');
                    $riskDisplayLevel = match ($riskLevel) {
                        'High' => 'Risiko Tinggi',
                        'Medium', 'Moderate' => 'Risiko Sedang',
                        'Low' => 'Risiko Rendah',
                        default => 'Tidak diketahui',
                    };
                    $riskIndex = session('risk_index_value');
                    $readinessScore = session('operational_readiness_score', session('sustainability_score'));
                    $shipmentData = session('shipment_data');
                    $drivers = session('explainability', []);
                    $topDrivers = array_slice($drivers, 0, 3);
                    $totalImpact = collect($drivers)->sum('impact');
                    $riskAssessment = session('risk_assessment', []);
                    $qualityPrediction = session('quality_prediction', []);
                    $recommendationPlan = session('recommendation_plan', []);

                    $urgencyRaw = (string) ($riskAssessment['urgency_level'] ?? '');
                    $urgencyIndo = match (strtolower($urgencyRaw)) {
                        'immediate' => 'Segera',
                        'high' => 'Tinggi',
                        'elevated', 'moderate' => 'Meningkat',
                        'routine', 'low' => 'Rutin',
                        default => $urgencyRaw ?: 'Rutin',
                    };

                    $statusIndoMap = fn (?string $st): string => match ($st) {
                        'Harvested' => 'Baru Dipanen',
                        'Packed' => 'Siap Kirim (Dikemas)',
                        'In Transit' => 'Dalam Perjalanan',
                        'Delivered' => 'Telah Tiba',
                        default => $st ?: 'Tidak diketahui',
                    };
                @endphp

                <div class="agri-card p-6 sm:p-8 lg:p-9 space-y-8 relative overflow-hidden border border-emerald-200/90">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600"></div>

                    {{-- Header Hasil Analisis (Selective Dark Navy Banner) --}}
                    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-emerald-950 p-5 sm:p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-300">Ringkasan Keputusan AI</p>
                                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Hasil Analisis Pengiriman</h2>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold border
                                {{ $riskLevel === 'High' ? 'bg-rose-500/20 text-rose-200 border-rose-400/40' : ($riskLevel === 'Medium' ? 'bg-amber-500/20 text-amber-200 border-amber-400/40' : 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40') }}">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                {{ $riskDisplayLevel }}
                            </span>

                            @if(!empty($riskAssessment['urgency_level']))
                                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold border
                                    {{ str_contains(strtolower($urgencyRaw), 'immediate') || str_contains(strtolower($urgencyRaw), 'high') ? 'bg-rose-500/20 text-rose-200 border-rose-400/40' :
                                       (str_contains(strtolower($urgencyRaw), 'elevated') || str_contains(strtolower($urgencyRaw), 'moderate') ? 'bg-amber-500/20 text-amber-200 border-amber-400/40' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40') }}">
                                    Urgensi: {{ $urgencyIndo }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- =========================================================
                         BAGIAN 1 — RINGKASAN PENGIRIMAN
                    ========================================================== --}}
                    @if($shipmentData)
                        <section aria-label="Ringkasan Pengiriman">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                {{-- Komoditas --}}
                                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-emerald-800 font-bold">Komoditas</p>
                                        <p class="text-base font-black text-slate-900 capitalize truncate">{{ $shipmentData['commodity'] ?? 'Belum tersedia' }}</p>
                                    </div>
                                </div>

                                {{-- Rute --}}
                                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-4 flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-blue-800 font-bold">Rute Pengiriman</p>
                                        <p class="text-sm font-black text-slate-900 capitalize truncate">
                                            {{ $shipmentData['origin'] ?? '—' }} <span class="text-blue-600 mx-1">→</span> {{ $shipmentData['destination'] ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Status Pengiriman --}}
                                <div class="rounded-2xl bg-teal-50 border border-teal-200 p-4 flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-teal-800 font-bold">Status Pengiriman</p>
                                        <p class="text-sm font-black text-slate-900 truncate">{{ $statusIndoMap($shipmentData['status'] ?? null) }}</p>
                                    </div>
                                </div>

                                {{-- Estimasi Waktu Perjalanan --}}
                                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-amber-900 font-bold">Estimasi Perjalanan</p>
                                        <p class="text-base font-black text-slate-900">
                                            {{ isset($shipmentData['duration']) && $shipmentData['duration'] !== null ? number_format((float) $shipmentData['duration'], 1, ',', '.') . ' jam' : 'Belum tersedia' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- =========================================================
                         BAGIAN 2 — KONDISI PENGIRIMAN (Partial Dipertahankan)
                    ========================================================== --}}
                    @include('ai-analysis.partials.freshness-result')

                    {{-- =========================================================
                         BAGIAN 3 — MENGAPA PENGIRIMAN INI PERLU DIPERHATIKAN?
                    ========================================================== --}}
                    @if(!empty($topDrivers))
                        <section aria-label="Faktor Risiko Utama" class="rounded-3xl bg-slate-50 border border-slate-200 p-6 space-y-5">
                            <div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900">Mengapa Pengiriman Ini Perlu Diperhatikan?</h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-1">{{ count($topDrivers) }} faktor utama yang paling memengaruhi tingkat risiko pengiriman saat ini.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($topDrivers as $driver)
                                    @php
                                        $impact = (float) ($driver['impact'] ?? 0);
                                        if ($impact >= 20) {
                                            $barColor = 'bg-rose-500';
                                            $cardTone = 'bg-rose-50/70 border-rose-200';
                                            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                            $severityText = 'Dampak Tinggi';
                                        } elseif ($impact >= 10) {
                                            $barColor = 'bg-amber-500';
                                            $cardTone = 'bg-amber-50/70 border-amber-200';
                                            $badgeClass = 'bg-amber-100 text-amber-900 border-amber-200';
                                            $severityText = 'Dampak Sedang';
                                        } elseif ($impact > 0) {
                                            $barColor = 'bg-cyan-500';
                                            $cardTone = 'bg-cyan-50/70 border-cyan-200';
                                            $badgeClass = 'bg-cyan-100 text-cyan-800 border-cyan-200';
                                            $severityText = 'Dampak Rendah';
                                        } else {
                                            $barColor = 'bg-slate-400';
                                            $cardTone = 'bg-white border-slate-200';
                                            $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                            $severityText = 'Minimal';
                                        }
                                        $barWidth = max(6, min(100, $impact * 2.5));
                                    @endphp

                                    <div class="rounded-2xl border p-5 flex flex-col justify-between {{ $cardTone }}">
                                        <div>
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold border {{ $badgeClass }}">
                                                    {{ $severityText }}
                                                </span>
                                                <span class="text-xs font-black text-slate-700">{{ number_format($impact, 1, ',', '.') }} poin</span>
                                            </div>

                                            <h4 class="text-sm font-black text-slate-900">{{ $driver['title'] ?? 'Faktor Risiko' }}</h4>
                                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $driver['reason'] ?? 'Memengaruhi tingkat risiko pengiriman saat ini.' }}</p>
                                        </div>

                                        <div class="mt-4 pt-3 border-t border-slate-200/70">
                                            <div class="h-2 rounded-full bg-white overflow-hidden border border-slate-200/60">
                                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $barWidth }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- =========================================================
                         BAGIAN 4 — REKOMENDASI AI & KEPUTUSAN RUTE (Partial Dipertahankan)
                    ========================================================== --}}
                    @include('ai-analysis.partials.intervention-recommendations')
                    @include('ai-analysis.partials.route-decision')

                    {{-- =========================================================
                         BAGIAN 5 — PETA RUTE PENGIRIMAN
                    ========================================================== --}}
                    @if($shipmentData)
                        <section aria-label="Peta Rute Pengiriman" class="rounded-3xl bg-blue-50/50 border border-blue-200/80 p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-black text-slate-900">Peta Rute Pengiriman</h3>
                                        <p class="text-xs text-slate-600">Jalur perjalanan dari lokasi asal menuju tujuan</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-emerald-200 text-slate-700 font-semibold">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        Asal: <strong class="text-slate-900 capitalize">{{ $shipmentData['origin'] ?? '—' }}</strong>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-blue-200 text-slate-700 font-semibold">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                        Tujuan: <strong class="text-slate-900 capitalize">{{ $shipmentData['destination'] ?? '—' }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div id="map" class="h-[320px] rounded-2xl overflow-hidden border border-blue-200 shadow-inner"></div>
                        </section>
                    @endif

{{-- =========================================================
                         BAGIAN LIPAT — LIHAT DETAIL TEKNIS
                    ========================================================== --}}
                    @php
                        $tempAssessment = session('temperature_assessment', []);
                        $tempStatus = $tempAssessment['status'] ?? null;
                        $tempMessage = $tempAssessment['message'] ?? null;
                        $transitMargin = $qualityPrediction['transit_margin_hours'] ?? null;
                        $isStorageStability = ($recommendationPlan['quality_model_type'] ?? null) === 'storage_stability';

                        $hasRefCondition = $isStorageStability
                            ? (($recommendationPlan['safe_moisture_short_term_max_percent'] ?? null) !== null || ($recommendationPlan['safe_relative_humidity_max_percent'] ?? null) !== null)
                            : (!empty($recommendationPlan['recommended_temperature_range']) || !empty($recommendationPlan['recommended_humidity_range']));

                        $remainingDrivers = array_slice($drivers, 3);

                        $tempStatusIndo = match ($tempStatus) {
                            'Optimal' => 'Optimal',
                            'Chilling risk' => 'Risiko Kerusakan Dingin',
                            'Above optimum' => 'Di Atas Suhu Ideal',
                            'Below optimum' => 'Di Bawah Suhu Ideal',
                            'Not provided' => 'Belum Dicatat',
                            default => $tempStatus ?? 'Belum Dicatat',
                        };
                    @endphp

                    <details class="group rounded-3xl bg-slate-50/90 border border-emerald-200/80 overflow-hidden transition-all shadow-2xs">
                        <summary class="cursor-pointer list-none flex items-center justify-between gap-4 px-6 py-5 bg-white hover:bg-emerald-50/40 transition-colors">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-slate-900 text-emerald-300 flex items-center justify-center shrink-0 shadow-sm">
                                    {{-- Lucide: sliders-horizontal --}}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/>
                                        <line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/>
                                        <line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/>
                                        <line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-base sm:text-lg font-black text-slate-900">Lihat Detail Teknis</span>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5">Ringkasan kondisi teknis, rincian faktor risiko lainnya, dan dasar penilaian AgriFlow.</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-extrabold shrink-0">
                                <span class="group-open:hidden">Buka Detail</span>
                                <span class="hidden group-open:inline">Tutup Detail</span>
                                <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </summary>

                        <div class="px-5 sm:px-6 pb-7 pt-6 border-t border-emerald-200/70 space-y-6">
                            {{-- A. Tiga Kartu Informasi Utama --}}
                            @if($tempStatus || $transitMargin !== null || $hasRefCondition)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- 1. Kondisi Suhu (Amber/Oranye) --}}
                                    @if($tempStatus)
                                        <div class="rounded-2xl bg-amber-50/80 border border-amber-200 p-5 flex flex-col justify-between">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-xs font-extrabold text-amber-900 uppercase tracking-wider">Kondisi Suhu</p>
                                                <div class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                                                    {{-- Lucide: thermometer --}}
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                        <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-xl font-black text-slate-900 mt-3 leading-tight">{{ $tempStatusIndo }}</p>
                                            <p class="text-xs sm:text-sm text-amber-950/80 mt-2 leading-relaxed">
                                                {{ $tempMessage ?: 'Berdasarkan data suhu muatan yang tercatat.' }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- 2. Waktu Cadangan Perjalanan (Biru) --}}
                                    @if($transitMargin !== null)
                                        <div class="rounded-2xl bg-blue-50/80 border border-blue-200 p-5 flex flex-col justify-between">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-xs font-extrabold text-blue-900 uppercase tracking-wider">Waktu Cadangan Perjalanan</p>
                                                <div class="w-9 h-9 rounded-xl bg-blue-100 border border-blue-200 text-blue-700 flex items-center justify-center shrink-0">
                                                    {{-- Lucide: clock --}}
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-xl font-black {{ (float) $transitMargin < 0 ? 'text-rose-700' : 'text-slate-900' }} mt-3 leading-tight">
                                                {{ number_format((float) $transitMargin, 1, ',', '.') }} jam
                                            </p>
                                            <p class="text-xs sm:text-sm text-blue-950/80 mt-2 leading-relaxed">
                                                {{ (float) $transitMargin < 0 ? 'Durasi perjalanan melebihi batas waktu aman.' : 'Sisa waktu aman di luar estimasi durasi perjalanan.' }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- 3. Standar Penyimpanan Ideal (Hijau Zamrud) --}}
                                    @if($hasRefCondition)
                                        <div class="rounded-2xl bg-emerald-50/80 border border-emerald-200 p-5 flex flex-col justify-between">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-xs font-extrabold text-emerald-900 uppercase tracking-wider">Standar Penyimpanan Ideal</p>
                                                <div class="w-9 h-9 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0">
                                                    {{-- Lucide: warehouse --}}
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                        <path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/>
                                                        <path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-xl font-black text-slate-900 mt-3 leading-tight">
                                                @if($isStorageStability)
                                                    @if(($recommendationPlan['safe_moisture_short_term_max_percent'] ?? null) !== null)
                                                        Kadar air ≤ {{ number_format($recommendationPlan['safe_moisture_short_term_max_percent'], 1, ',', '.') }}%
                                                    @elseif(($recommendationPlan['safe_relative_humidity_max_percent'] ?? null) !== null)
                                                        Kelembapan ≤ {{ number_format($recommendationPlan['safe_relative_humidity_max_percent'], 0, ',', '.') }}%
                                                    @endif
                                                @else
                                                    {{ $recommendationPlan['recommended_temperature_range'] ?? $recommendationPlan['recommended_humidity_range'] }}
                                                @endif
                                            </p>
                                            <p class="text-xs sm:text-sm text-emerald-950/80 mt-2 leading-relaxed">
                                                Acuan penyimpanan yang disarankan untuk komoditas ini.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- B. Faktor Risiko Lainnya (Visual Cards) --}}
                            @if(!empty($remainingDrivers))
                                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-teal-300 flex items-center justify-center shrink-0">
                                                {{-- Lucide: layers --}}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-black text-slate-900">Faktor Risiko Lainnya</h4>
                                                <p class="text-xs text-slate-500">Kontribusi tambahan di luar 3 faktor utama.</p>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-900 text-white text-xs font-extrabold">
                                            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                                            Total Kontribusi: {{ number_format($totalImpact, 1, ',', '.') }} poin
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        @foreach($remainingDrivers as $driver)
                                            @php
                                                $dImpact = (float) ($driver['impact'] ?? 0);
                                                $dTitle = (string) ($driver['title'] ?? 'Faktor Risiko');
                                                $dLower = strtolower($dTitle);

                                                // Warna kartu berdasarkan tingkat kontribusi
                                                if ($dImpact >= 15) {
                                                    $dTone = ['card' => 'bg-rose-50/70 border-rose-200', 'icon' => 'bg-rose-100 text-rose-700', 'badge' => 'bg-rose-600 text-white'];
                                                } elseif ($dImpact >= 7) {
                                                    $dTone = ['card' => 'bg-amber-50/70 border-amber-200', 'icon' => 'bg-amber-100 text-amber-800', 'badge' => 'bg-amber-500 text-white'];
                                                } elseif ($dImpact > 0) {
                                                    $dTone = ['card' => 'bg-blue-50/70 border-blue-200', 'icon' => 'bg-blue-100 text-blue-700', 'badge' => 'bg-blue-600 text-white'];
                                                } else {
                                                    $dTone = ['card' => 'bg-teal-50/60 border-teal-200', 'icon' => 'bg-teal-100 text-teal-700', 'badge' => 'bg-teal-600 text-white'];
                                                }

                                                // Pilih ikon sesuai jenis faktor
                                                $iconType = match (true) {
                                                    str_contains($dLower, 'suhu') || str_contains($dLower, 'temp') => 'temp',
                                                    str_contains($dLower, 'waktu') || str_contains($dLower, 'margin') || str_contains($dLower, 'tunda') => 'clock',
                                                    str_contains($dLower, 'tahap') || str_contains($dLower, 'status') || str_contains($dLower, 'kemas') => 'package',
                                                    default => 'route',
                                                };
                                            @endphp

                                            <div class="rounded-xl border p-4 flex items-start gap-3.5 {{ $dTone['card'] }}">
                                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $dTone['icon'] }}">
                                                    @if($iconType === 'temp')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                                                    @elseif($iconType === 'clock')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                    @elseif($iconType === 'package')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>
                                                    @endif
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <p class="text-sm font-black text-slate-900 leading-snug">{{ $dTitle }}</p>
                                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold shrink-0 {{ $dTone['badge'] }}">
                                                            {{ number_format($dImpact, 1, ',', '.') }} poin
                                                        </span>
                                                    </div>
                                                    @if(!empty($driver['reason']))
                                                        <p class="text-xs sm:text-[13px] text-slate-700 mt-1.5 leading-relaxed">{{ $driver['reason'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- C. Tentang Analisis Ini (Partial) --}}
                            @include('ai-analysis.partials.risk-engine-result')
                        </div>
                    </details>
                </div>
            @endif

            {{-- ================= DAFTAR PENGIRIMAN AKTIF UNTUK DIANALISIS ================= --}}
            <div class="agri-card p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Pengiriman Aktif untuk Dianalisis</h2>
                    <p class="text-sm text-slate-600 mt-1">Pilih salah satu pengiriman di bawah ini untuk menjalankan penilaian risiko dan kondisi produk terbaru.</p>
                </div>

                @if($shipments->isNotEmpty())
                    <div class="space-y-3.5">
                        @foreach($shipments as $index => $shipment)
                            @php
                                $themes = [
                                    0 => 'bg-emerald-50/60 border-emerald-200/80 hover:bg-emerald-50',
                                    1 => 'bg-teal-50/60 border-teal-200/80 hover:bg-teal-50',
                                    2 => 'bg-sky-50/60 border-sky-200/80 hover:bg-sky-50',
                                    3 => 'bg-indigo-50/50 border-indigo-200/80 hover:bg-indigo-50/80',
                                ];
                                $theme = $themes[$index % 4];

                                $statusBadge = match ($shipment->status) {
                                    'Harvested' => ['label' => 'Baru Dipanen', 'classes' => 'bg-amber-100 text-amber-800 border-amber-200'],
                                    'Packed' => ['label' => 'Siap Kirim', 'classes' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                    'In Transit' => ['label' => 'Dalam Perjalanan', 'classes' => 'bg-teal-100 text-teal-800 border-teal-200'],
                                    'Delivered' => ['label' => 'Telah Tiba', 'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                                    default => ['label' => $shipment->status, 'classes' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                };
                            @endphp

                            <div class="agri-row rounded-2xl border p-4 sm:p-5 {{ $theme }}">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                    {{-- Komoditas --}}
                                    <div class="lg:col-span-3 flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-white border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-[11px] font-bold text-slate-500">#{{ $shipment->id }}</span>
                                            <p class="font-black text-slate-900 text-base capitalize truncate">{{ $shipment->harvest->commodity ?? 'Tidak diketahui' }}</p>
                                        </div>
                                    </div>

                                    {{-- Rute --}}
                                    <div class="lg:col-span-5 flex items-center gap-2 text-sm font-bold text-slate-800 min-w-0">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        <span class="capitalize truncate">{{ $shipment->origin }}</span>
                                        <span class="text-emerald-600 font-black shrink-0">→</span>
                                        <span class="capitalize truncate">{{ $shipment->destination }}</span>
                                    </div>

                                    {{-- Status --}}
                                    <div class="lg:col-span-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-extrabold border {{ $statusBadge['classes'] }}">
                                            {{ $statusBadge['label'] }}
                                        </span>
                                    </div>

                                    {{-- Tombol Analisis --}}
                                    <div class="lg:col-span-2 flex lg:justify-end">
                                        <form action="{{ route('ai.analysis.run', $shipment->id) }}" method="POST" onsubmit="return showLoading(this)" class="w-full lg:w-auto">
                                            @csrf
                                            <button type="submit"
                                                    class="btn-animate w-full lg:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-5 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                <span>Analisis Sekarang</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 px-6 text-center rounded-2xl bg-emerald-50/60 border border-dashed border-emerald-300">
                        <p class="text-base font-black text-slate-900">Belum Ada Pengiriman Aktif</p>
                        <p class="text-sm text-slate-600 mt-1">Buat pengiriman terlebih dahulu agar Anda dapat menjalankan Analisis AI.</p>
                        <a href="{{ route('shipments.create') }}"
                           class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors">
                            + Buat Pengiriman
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Inisialisasi Peta Leaflet --}}
    @php
        $shipmentData = session('shipment_data');
    @endphp

    @if($shipmentData)
        @php
            $routeGeometry = json_decode($shipmentData['route_geometry'], true);
        @endphp
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const originLat = {{ $shipmentData['origin_lat'] }};
            const originLng = {{ $shipmentData['origin_lng'] }};
            const destinationLat = {{ $shipmentData['destination_lat'] }};
            const destinationLng = {{ $shipmentData['destination_lng'] }};
            const originLabel = @json($shipmentData['origin'] ?? 'Asal');
            const destinationLabel = @json($shipmentData['destination'] ?? 'Tujuan');

            const map = L.map('map').setView([originLat, originLng], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const originIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div style="width:16px;height:16px;border-radius:9999px;background:#059669;border:3px solid #ffffff;box-shadow:0 0 0 4px rgba(5,150,105,0.3);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            const destinationIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div style="width:16px;height:16px;border-radius:9999px;background:#2563EB;border:3px solid #ffffff;box-shadow:0 0 0 4px rgba(37,99,235,0.3);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            L.marker([originLat, originLng], { icon: originIcon })
                .addTo(map)
                .bindPopup(`<strong>Lokasi Asal</strong><br>${originLabel}`);

            L.marker([destinationLat, destinationLng], { icon: destinationIcon })
                .addTo(map)
                .bindPopup(`<strong>Lokasi Tujuan</strong><br>${destinationLabel}`);

            const routeCoords = @json($routeGeometry);
            if (Array.isArray(routeCoords) && routeCoords.length > 0) {
                const latlngs = routeCoords.map(coord => [coord[1], coord[0]]);
                const routeLine = L.polyline(latlngs, {
                    color: '#059669',
                    weight: 5,
                    opacity: 0.9
                }).addTo(map);

                map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
            }
        });
        </script>
    @endif

    {{-- Script Loading --}}
    <script>
    function showLoading(form) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        const btn = form.querySelector("button[type='submit']");
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" opacity=".2"/>
                    <path d="M22 12a10 10 0 00-10-10" stroke="white" stroke-width="3"/>
                </svg>
                <span>Menganalisis...</span>
            `;
        }
        return true;
    }
    </script>

    {{-- Overlay Loading AI --}}
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-950/75 backdrop-blur-md hidden items-center justify-center z-[9999]">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl p-10 w-[420px] max-w-[90vw] text-center shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 animate-pulse"></div>
            <div class="flex justify-center mb-6">
                <div class="relative w-16 h-16">
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-900"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin"></div>
                </div>
            </div>
            <h2 class="text-2xl font-black text-white">Sedang Menganalisis Pengiriman...</h2>
            <p class="text-slate-300 mt-3 text-xs leading-relaxed">
                Memeriksa kondisi kesegaran produk, faktor risiko perjalanan, dan menyusun rekomendasi tindakan terbaik.
            </p>
            <div class="mt-8">
                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>