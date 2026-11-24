<x-app-layout>
    @php
        $baseline = $comparisonSet->baseline_snapshot ?? [];
        $scenarios = $comparisonSet->scenarios_snapshot ?? [];
        $comparison = $comparisonSet->comparison_snapshot ?? [];

        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $createdAtFormatted = $comparisonSet->created_at
            ? $comparisonSet->created_at->format('d') . ' ' . ($bulanIndo[(int) $comparisonSet->created_at->format('n')] ?? $comparisonSet->created_at->format('M')) . ' ' . $comparisonSet->created_at->format('Y · H:i')
            : '—';

        $commodity = $comparisonSet->shipment?->harvest?->commodity ?? null;

        // Helper lokalisasi tampilan nama skenario (hanya presentasi frontend, tidak mengubah data asli)
        $translateScenarioName = function (?string $name, ?int $idx = null): string {
            $clean = trim((string) $name);
            if ($clean === '' || strcasecmp($clean, 'Current Plan') === 0) {
                return $idx === null ? 'Rencana Saat Ini' : ('Skenario ' . chr(65 + $idx));
            }

            $replacements = [
                '/\bScenario\s+([A-Z0-9]+)/i' => 'Skenario $1',
                '/Current Conditions/i' => 'Kondisi Saat Ini',
                '/Delayed Delivery/i' => 'Pengiriman Tertunda',
                '/Adjusted Conditions/i' => 'Kondisi Disesuaikan',
                '/Current Plan/i' => 'Rencana Saat Ini',
            ];

            return (string) preg_replace(array_keys($replacements), array_values($replacements), $clean);
        };

        // Gabungkan Current Plan + semua skenario
        $allOptions = array_merge(
            [[
                'key' => 'current_plan',
                'raw_name' => 'Current Plan',
                'display_name' => 'Rencana Saat Ini',
                'is_baseline' => true,
                'snapshot' => $baseline,
            ]],
            array_map(
                fn ($scenario, $idx) => [
                    'key' => 'scenario_' . $idx,
                    'raw_name' => $scenario['name'] ?? ('Scenario ' . chr(65 + $idx)),
                    'display_name' => $translateScenarioName($scenario['name'] ?? null, $idx),
                    'is_baseline' => false,
                    'snapshot' => $scenario,
                ],
                $scenarios,
                array_keys($scenarios)
            )
        );

        $preferredOptionType = data_get($comparison, 'preferred_option', 'current_plan');
        $recommendedScenarioName = data_get($comparison, 'recommended_scenario.name');

        // Tentukan opsi terpilih (Preferred)
        $preferredOptionData = $allOptions[0];
        foreach ($allOptions as $opt) {
            $isPref = $preferredOptionType === 'current_plan'
                ? $opt['is_baseline']
                : (!$opt['is_baseline'] && $recommendedScenarioName === $opt['raw_name']);
            if ($isPref) {
                $preferredOptionData = $opt;
                break;
            }
        }

        // Mapping status keputusan ke Bahasa Indonesia & warna
        $decisionType = data_get($comparison, 'decision_type', 'equivalent_alternative');
        $rawDecisionStatus = data_get($comparison, 'decision_status', 'Keputusan Tersimpan');

        $statusConfig = match ($decisionType) {
            'recommended_over_current' => [
                'label' => 'Lebih Direkomendasikan',
                'badge' => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30',
                'dot' => 'bg-emerald-400',
            ],
            'equivalent_alternative' => [
                'label' => 'Alternatif Setara',
                'badge' => 'bg-cyan-500/15 text-cyan-300 border-cyan-400/30',
                'dot' => 'bg-cyan-400',
            ],
            'conditional' => [
                'label' => 'Skenario Bersyarat',
                'badge' => 'bg-amber-500/15 text-amber-300 border-amber-400/30',
                'dot' => 'bg-amber-400',
            ],
            'viable_but_not_recommended' => [
                'label' => 'Layak, Namun Rencana Saat Ini Lebih Baik',
                'badge' => 'bg-amber-500/15 text-amber-300 border-amber-400/30',
                'dot' => 'bg-amber-400',
            ],
            'insufficient_evidence' => [
                'label' => 'Data Rute Belum Mencukupi',
                'badge' => 'bg-amber-500/15 text-amber-300 border-amber-400/30',
                'dot' => 'bg-amber-400',
            ],
            'not_viable' => [
                'label' => 'Tidak Layak Dijalankan',
                'badge' => 'bg-rose-500/15 text-rose-300 border-rose-400/30',
                'dot' => 'bg-rose-400',
            ],
            default => [
                'label' => $rawDecisionStatus,
                'badge' => 'bg-indigo-500/15 text-indigo-300 border-indigo-400/30',
                'dot' => 'bg-indigo-400',
            ],
        };

        // Helper translasi kelayakan rute
        $translateFeasibility = function (?string $val): array {
            return match ($val) {
                'Safe' => ['label' => 'Aman', 'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
                'Tight' => ['label' => 'Ketat', 'classes' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
                'Breach' => ['label' => 'Melewati Batas', 'classes' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500'],
                default => ['label' => 'Tidak Tersedia', 'classes' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400'],
            };
        };

        // Terjemahkan penjelasan AI secara dinamis dari pola ScenarioComparisonService
        $rawReason = data_get($comparison, 'decision_reason', '');
        $translatedReason = $rawReason;

        if (str_contains($rawReason, 'breaches the current operational freshness window')) {
            $translatedReason = 'Skenario ini melewati batas waktu kesegaran produk sehingga tidak direkomendasikan dibandingkan rencana saat ini.';
        } elseif (str_contains($rawReason, 'Route feasibility cannot be reliably compared')) {
            $translatedReason = 'Kelayakan rute belum dapat dibandingkan secara akurat karena data waktu tempuh belum lengkap. Pertahankan rencana saat ini hingga data tersedia.';
        } elseif (preg_match('/improves route feasibility from (\w+) to (\w+)/i', $rawReason, $m)) {
            $fromF = $translateFeasibility($m[1])['label'];
            $toF = $translateFeasibility($m[2])['label'];
            $translatedReason = "Skenario ini meningkatkan kelayakan rute dari {$fromF} menjadi {$toF}, sehingga menjadi pilihan utama sebelum mempertimbangkan faktor lainnya.";
        } elseif (preg_match('/reduces route feasibility from (\w+) to (\w+)/i', $rawReason, $m)) {
            $fromF = $translateFeasibility($m[1])['label'];
            $toF = $translateFeasibility($m[2])['label'];
            $translatedReason = "Skenario ini menurunkan kelayakan rute dari {$fromF} menjadi {$toF}, sehingga rencana saat ini tetap lebih baik.";
        } elseif (preg_match('/reducing operational risk by ([\d\.]+) point\(s\), from ([^\/]+)\/100 to ([^\/]+)\/100/i', $rawReason, $m)) {
            $translatedReason = "Skenario ini menjaga kelayakan rute tetap baik sekaligus menurunkan tingkat risiko sebesar {$m[1]} poin (dari {$m[2]}/100 menjadi {$m[3]}/100).";
        } elseif (preg_match('/increases operational risk by ([\d\.]+) point\(s\), from ([^\/]+)\/100 to ([^\/]+)\/100/i', $rawReason, $m)) {
            $translatedReason = "Skenario ini masih layak secara rute, tetapi meningkatkan tingkat risiko sebesar {$m[1]} poin (dari {$m[2]}/100 menjadi {$m[3]}/100). Rencana saat ini tetap lebih disarankan.";
        } elseif (preg_match('/estimated arrival quality improves by ([\d\.]+) point\(s\)/i', $rawReason, $m)) {
            $translatedReason = "Tingkat risiko relatif sama, tetapi perkiraan kondisi produk saat tiba meningkat sebesar {$m[1]} poin.";
        } elseif (preg_match('/estimated arrival quality decreases by ([\d\.]+) point\(s\)/i', $rawReason, $m)) {
            $translatedReason = "Tingkat risiko relatif sama, tetapi perkiraan kondisi produk saat tiba menurun sebesar {$m[1]} poin sehingga rencana saat ini lebih baik.";
        } elseif (preg_match('/transit margin improves by ([\d\.]+) hour\(s\)/i', $rawReason, $m)) {
            $translatedReason = "Kondisi dan risiko produk relatif sama, tetapi skenario ini memberikan tambahan waktu cadangan pengiriman sebesar {$m[1]} jam.";
        } elseif (preg_match('/transit margin decreases by ([\d\.]+) hour\(s\)/i', $rawReason, $m)) {
            $translatedReason = "Tingkat risiko relatif sama, tetapi waktu cadangan pengiriman berkurang sebesar {$m[1]} jam sehingga rencana saat ini tetap lebih baik.";
        } elseif (preg_match('/estimated carbon is ([\d\.]+) kg lower/i', $rawReason, $m)) {
            $translatedReason = "Risiko, kondisi produk, dan waktu cadangan relatif setara, tetapi skenario ini menghasilkan emisi karbon {$m[1]} kg lebih rendah.";
        } elseif (preg_match('/increases estimated carbon by ([\d\.]+) kg/i', $rawReason, $m)) {
            $translatedReason = "Skenario ini tidak memberikan peningkatan operasional yang berarti dan justru menambah perkiraan emisi karbon sebesar {$m[1]} kg.";
        } elseif (str_contains($rawReason, 'does not provide a material improvement')) {
            $translatedReason = 'Skenario ini tetap layak dijalankan, namun tidak memberikan peningkatan yang signifikan dibandingkan rencana saat ini.';
        } elseif (empty($rawReason)) {
            $translatedReason = 'Ringkasan perbandingan simulasi yang tersimpan.';
        }

        // Metrik Utama dari Pilihan Terbaik (Preferred)
        $prefSnap = $preferredOptionData['snapshot'];
        $prefRisk = data_get($prefSnap, 'analysis.risk_score');
        $prefIsDry = data_get($prefSnap, 'analysis.quality_prediction.condition_model_type') === 'storage_stability';
        $prefQuality = data_get($prefSnap, 'analysis.quality_at_arrival');
        $prefStorageStatus = data_get($prefSnap, 'analysis.quality_prediction.storage_stability_assessment.status');
        $prefFeasibility = $translateFeasibility(data_get($prefSnap, 'route.freshness_feasibility'));
        $prefMargin = data_get($prefSnap, 'route.transit_margin_hours');
        $prefCarbon = data_get($prefSnap, 'carbon.estimated_kg');
        $prefEvidence = data_get($prefSnap, 'evidence.percent');

        // Data Delta untuk Section D (Kenapa AI memilih skenario ini)
        $baselineComparison = data_get($comparison, 'baseline_comparison', []);
        $delta = data_get($baselineComparison, 'delta', []);
        $bestScenarioName = $translateScenarioName(data_get($comparison, 'best_available_scenario.name', 'Skenario Terbaik'));

        // Nilai maksimum untuk skala chart visual (Section E)
        $maxMarginAbs = collect($allOptions)
            ->map(fn ($o) => is_numeric(data_get($o['snapshot'], 'route.transit_margin_hours')) ? abs((float) data_get($o['snapshot'], 'route.transit_margin_hours')) : 0)
            ->max();
        $maxCarbon = collect($allOptions)
            ->map(fn ($o) => is_numeric(data_get($o['snapshot'], 'carbon.estimated_kg')) ? (float) data_get($o['snapshot'], 'carbon.estimated_kg') : 0)
            ->max();

        /*
         * Helper Gradasi Warna Dinamis (Presentasi Frontend Saja)
         * Semakin besar nilai parameter, semakin pekat/gelap warna indikatornya.
         */

        // 1. Skor Risiko (0-100): Kuning terang -> Oranye -> Merah gelap
        $riskGradient = function ($val): array {
            if (!is_numeric($val)) {
                return ['bar' => 'bg-slate-300', 'text' => 'text-slate-500'];
            }
            $v = (float) $val;
            if ($v < 35) {
                return ['bar' => 'bg-gradient-to-r from-amber-200 to-amber-400', 'text' => 'text-amber-700'];
            }
            if ($v < 65) {
                return ['bar' => 'bg-gradient-to-r from-amber-400 to-orange-500', 'text' => 'text-orange-700'];
            }
            return ['bar' => 'bg-gradient-to-r from-orange-600 to-red-800', 'text' => 'text-rose-800'];
        };

        // 2. Kondisi Produk (0-100): Hijau muda -> Hijau -> Hijau tua
        $qualityGradient = function ($val): array {
            if (!is_numeric($val)) {
                return ['bar' => 'bg-slate-300', 'text' => 'text-slate-500'];
            }
            $v = (float) $val;
            if ($v < 50) {
                return ['bar' => 'bg-gradient-to-r from-emerald-200 to-emerald-400', 'text' => 'text-emerald-700'];
            }
            if ($v < 75) {
                return ['bar' => 'bg-gradient-to-r from-emerald-400 to-emerald-600', 'text' => 'text-emerald-700'];
            }
            return ['bar' => 'bg-gradient-to-r from-emerald-600 to-emerald-800', 'text' => 'text-emerald-900'];
        };

        // 3. Waktu Cadangan (jam): Biru muda -> Biru -> Biru tua (Merah jika negatif)
        $marginGradient = function ($val, float $maxAbs): array {
            if (!is_numeric($val)) {
                return ['bar' => 'bg-slate-300', 'text' => 'text-slate-500'];
            }
            $v = (float) $val;
            if ($v < 0) {
                return ['bar' => 'bg-gradient-to-r from-rose-500 to-red-700', 'text' => 'text-rose-700'];
            }
            $ratio = $maxAbs > 0 ? ($v / $maxAbs) : 0;
            if ($ratio < 0.4) {
                return ['bar' => 'bg-gradient-to-r from-sky-300 to-sky-400', 'text' => 'text-sky-700'];
            }
            if ($ratio < 0.75) {
                return ['bar' => 'bg-gradient-to-r from-blue-400 to-blue-600', 'text' => 'text-blue-700'];
            }
            return ['bar' => 'bg-gradient-to-r from-blue-600 to-indigo-800', 'text' => 'text-blue-900'];
        };

        // 4. Emisi Karbon (kg): Hijau muda -> Hijau toska -> Hijau tua
        $carbonGradient = function ($val, float $maxVal): array {
            if (!is_numeric($val)) {
                return ['bar' => 'bg-slate-300', 'text' => 'text-slate-500'];
            }
            $v = (float) $val;
            $ratio = $maxVal > 0 ? ($v / $maxVal) : 0;
            if ($ratio < 0.4) {
                return ['bar' => 'bg-gradient-to-r from-emerald-200 to-teal-300', 'text' => 'text-teal-700'];
            }
            if ($ratio < 0.75) {
                return ['bar' => 'bg-gradient-to-r from-teal-400 to-teal-600', 'text' => 'text-teal-800'];
            }
            return ['bar' => 'bg-gradient-to-r from-teal-700 to-emerald-900', 'text' => 'text-emerald-950'];
        };
    @endphp

    <div class="min-h-screen bg-gradient-to-b from-slate-50 via-cyan-50/25 to-emerald-50/20 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">

            {{-- ================= TOP NAVIGATION BAR ================= --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('digital-twin.comparisons.history') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200/90 text-slate-700 hover:text-cyan-700 hover:border-cyan-300 shadow-sm transition-all duration-200 hover:-translate-x-0.5 text-sm font-extrabold">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Riwayat Perbandingan
                </a>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-sky-50 border border-sky-200/80 text-sky-700 text-sm font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Pengiriman #{{ $comparisonSet->shipment_id }}
                    </span>
                    @if($commodity)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-sm font-bold capitalize">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z" />
                            </svg>
                            {{ $commodity }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- =====================================================================
                 SECTION A: AI RECOMMENDATION OVERVIEW (Dark Navy Centerpiece)
            ===================================================================== --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-6 sm:p-8 lg:p-10 text-white shadow-2xl shadow-slate-900/15 border border-slate-800">
                <div class="pointer-events-none absolute -top-24 -right-20 w-72 h-72 rounded-full bg-emerald-500/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-16 w-72 h-72 rounded-full bg-cyan-500/15 blur-3xl"></div>

                <div class="relative z-10 space-y-6">
                    {{-- Header Row --}}
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 via-cyan-500 to-indigo-600 p-0.5 shadow-lg shadow-cyan-500/25">
                                <div class="w-full h-full rounded-[14px] bg-slate-900/85 backdrop-blur-sm flex items-center justify-center text-emerald-300">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/>
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-300">
                                    Detail Perbandingan · Operational Digital Twin
                                </p>
                                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight mt-1">
                                    {{ $comparisonSet->name }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-300 font-medium mt-2">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $createdAtFormatted }}
                                    </span>
                                    <span class="text-slate-500">•</span>
                                    <span>Mesin {{ $comparisonSet->engine_version }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border text-sm font-extrabold {{ $statusConfig['badge'] }}">
                                <span class="w-2.5 h-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Highlighted Preferred Decision + AI Explanation --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 pt-2">
                        <div class="lg:col-span-5 rounded-2xl bg-gradient-to-br from-emerald-500/20 via-emerald-500/10 to-teal-500/10 border border-emerald-400/30 p-5 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-emerald-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Rekomendasi AI
                                </span>
                                <p class="text-xl sm:text-2xl font-black text-white mt-2 leading-snug">
                                    {{ $preferredOptionData['display_name'] }}
                                </p>
                            </div>
                            <p class="text-sm text-emerald-100/90 mt-3 font-medium leading-relaxed">
                                {{ $preferredOptionData['is_baseline']
                                    ? 'Pertahankan rencana pengiriman saat ini sebagai opsi paling optimal.'
                                    : 'Skenario ini dipilih sebagai opsi terbaik dari seluruh simulasi yang dibandingkan.' }}
                            </p>
                        </div>

                        <div class="lg:col-span-7 rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur-sm flex flex-col justify-center">
                            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-indigo-300 mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Ringkasan Keputusan AI
                            </div>
                            <p class="text-base text-slate-100 leading-relaxed font-medium">
                                {{ $translatedReason }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =====================================================================
                 SECTION B: KEY METRICS OVERVIEW (Preferred Option Metrics)
            ===================================================================== --}}
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 px-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-6 rounded-full bg-gradient-to-b from-emerald-500 to-cyan-500"></span>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900">
                            Ringkasan Metrik Pilihan Terbaik
                        </h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3.5 py-1 rounded-full w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $preferredOptionData['display_name'] }}
                    </span>
                </div>

                @php
                    $riskVal = is_numeric($prefRisk) ? (float) $prefRisk : null;
                    $riskTheme = $riskVal === null
                        ? ['card' => 'from-slate-50 to-white border-slate-200', 'icon' => 'from-slate-500 to-slate-600', 'text' => 'text-slate-700', 'sub' => 'Belum tersedia']
                        : ($riskVal >= 60
                            ? ['card' => 'from-rose-50 via-white to-red-50/60 border-rose-200/80', 'icon' => 'from-rose-500 to-red-600', 'text' => 'text-rose-700', 'sub' => 'Perlu perhatian tinggi']
                            : ($riskVal >= 35
                                ? ['card' => 'from-amber-50 via-white to-orange-50/60 border-amber-200/80', 'icon' => 'from-amber-500 to-orange-500', 'text' => 'text-amber-700', 'sub' => 'Risiko tingkat sedang']
                                : ['card' => 'from-emerald-50 via-white to-teal-50/60 border-emerald-200/80', 'icon' => 'from-emerald-500 to-teal-600', 'text' => 'text-emerald-700', 'sub' => 'Risiko tergolong rendah']));
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- 1. Tingkat Risiko --}}
                    <div class="rounded-2xl bg-gradient-to-br {{ $riskTheme['card'] }} border p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide {{ $riskTheme['text'] }}">Tingkat Risiko</p>
                                <p class="text-3xl font-black text-slate-900 mt-1.5">
                                    {{ $riskVal !== null ? $prefRisk : '—' }}<span class="text-base font-bold text-slate-400">/100</span>
                                </p>
                                <p class="text-[13px] text-slate-600 mt-1.5">{{ $riskTheme['sub'] }} (makin kecil makin baik)</p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $riskTheme['icon'] }} text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kondisi Produk --}}
                    <div class="rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-green-50/60 border border-emerald-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">Kondisi Produk</p>
                                @if($prefIsDry)
                                    <p class="text-xl font-black text-slate-900 mt-2 leading-tight">
                                        {{ $prefStorageStatus ?? 'Perlu data kondisi' }}
                                    </p>
                                    <p class="text-[13px] text-slate-600 mt-1.5">Status stabilitas penyimpanan</p>
                                @else
                                    <p class="text-3xl font-black text-slate-900 mt-1.5">
                                        {{ $prefQuality !== null ? $prefQuality : '—' }}<span class="text-base font-bold text-slate-400">/100</span>
                                    </p>
                                    <p class="text-[13px] text-slate-600 mt-1.5">Perkiraan kesegaran saat tiba</p>
                                @endif
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Kelayakan Pengiriman --}}
                    <div class="rounded-2xl bg-gradient-to-br from-cyan-50 via-white to-sky-50/60 border border-cyan-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">Kelayakan Pengiriman</p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border text-sm font-extrabold {{ $prefFeasibility['classes'] }}">
                                        <span class="w-2 h-2 rounded-full {{ $prefFeasibility['dot'] }}"></span>
                                        {{ $prefFeasibility['label'] }}
                                    </span>
                                </div>
                                <p class="text-[13px] text-slate-600 mt-2">Status keamanan rute terhadap kesegaran</p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2m10 0H9m10 0h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14" />
                                    <circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Waktu Cadangan --}}
                    <div class="rounded-2xl bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 border border-blue-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-blue-700">Waktu Cadangan</p>
                                <p class="text-3xl font-black text-slate-900 mt-1.5">
                                    @if(is_numeric($prefMargin))
                                        {{ number_format((float) $prefMargin, 1) }} <span class="text-base font-bold text-slate-500">jam</span>
                                    @else
                                        <span class="text-lg text-slate-400">Tidak tersedia</span>
                                    @endif
                                </p>
                                <p class="text-[13px] text-slate-600 mt-1.5">Sisa waktu aman sebelum batas kesegaran</p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Emisi Karbon --}}
                    <div class="rounded-2xl bg-gradient-to-br from-teal-50 via-white to-emerald-50/60 border border-teal-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Emisi Karbon</p>
                                <p class="text-3xl font-black text-slate-900 mt-1.5">
                                    @if(is_numeric($prefCarbon))
                                        {{ number_format((float) $prefCarbon, 2) }} <span class="text-base font-bold text-slate-500">kg</span>
                                    @else
                                        <span class="text-lg text-slate-400">Tidak tersedia</span>
                                    @endif
                                </p>
                                <p class="text-[13px] text-slate-600 mt-1.5">Perkiraan jejak karbon pengiriman</p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Kelengkapan Data --}}
                    <div class="rounded-2xl bg-gradient-to-br from-purple-50 via-white to-fuchsia-50/60 border border-purple-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-purple-700">Kelengkapan Data</p>
                                <p class="text-3xl font-black text-slate-900 mt-1.5">
                                    {{ $prefEvidence !== null ? $prefEvidence . '%' : '—' }}
                                </p>
                                <p class="text-[13px] text-slate-600 mt-1.5">Cakupan data pendukung analisis ini</p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =====================================================================
                 SECTION C: SCENARIO COMPARISON (Desktop Table + Mobile Cards)
            ===================================================================== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4 px-1">
                    <span class="w-2.5 h-6 rounded-full bg-gradient-to-b from-indigo-500 to-purple-500"></span>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900">
                        Perbandingan Lengkap Skenario
                    </h2>
                </div>

                {{-- Desktop Table (lg+) --}}
                <div class="hidden lg:block overflow-hidden rounded-3xl bg-white border border-slate-200/90 shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[13px] font-extrabold uppercase tracking-wider text-slate-600">
                                <th class="py-4 px-5">Pilihan Skenario</th>
                                <th class="py-4 px-4">Tingkat Risiko</th>
                                <th class="py-4 px-4">Kondisi Produk</th>
                                <th class="py-4 px-4">Kelayakan</th>
                                <th class="py-4 px-4">Waktu Cadangan</th>
                                <th class="py-4 px-4">Emisi Karbon</th>
                                <th class="py-4 px-5">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($allOptions as $option)
                                @php
                                    $snap = $option['snapshot'];
                                    $isPreferred = $option['key'] === $preferredOptionData['key'];
                                    $risk = data_get($snap, 'analysis.risk_score');
                                    $isDry = data_get($snap, 'analysis.quality_prediction.condition_model_type') === 'storage_stability';
                                    $quality = data_get($snap, 'analysis.quality_at_arrival');
                                    $storageStatus = data_get($snap, 'analysis.quality_prediction.storage_stability_assessment.status', 'Perlu data kondisi');
                                    $feas = $translateFeasibility(data_get($snap, 'route.freshness_feasibility'));
                                    $margin = data_get($snap, 'route.transit_margin_hours');
                                    $carbon = data_get($snap, 'carbon.estimated_kg');
                                    $evidence = data_get($snap, 'evidence.percent');

                                    $rGrad = $riskGradient($risk);
                                    $qGrad = $qualityGradient($quality);
                                @endphp

                                <tr class="transition-colors {{ $isPreferred ? 'bg-emerald-50/60 hover:bg-emerald-50' : 'hover:bg-slate-50/80' }}">
                                    {{-- Pilihan Skenario --}}
                                    <td class="py-4 px-5 {{ $isPreferred ? 'border-l-4 border-emerald-500' : 'border-l-4 border-transparent' }}">
                                        <p class="font-extrabold text-slate-900 text-[15px] leading-snug">
                                            {{ $option['display_name'] }}
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            @if($isPreferred)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-600 text-white text-xs font-extrabold tracking-wide shadow-sm">
                                                    ★ Pilihan Terbaik AI
                                                </span>
                                            @elseif($option['is_baseline'])
                                                <span class="inline-flex px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">
                                                    Baseline
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Tingkat Risiko --}}
                                    <td class="py-4 px-4">
                                        <div class="w-32">
                                            <div class="flex items-baseline justify-between text-[15px] font-extrabold {{ $rGrad['text'] }} mb-1.5">
                                                <span>{{ is_numeric($risk) ? $risk : '—' }}</span>
                                                <span class="text-xs font-semibold text-slate-400">/100</span>
                                            </div>
                                            <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full {{ $rGrad['bar'] }}" style="width: {{ is_numeric($risk) ? max(0, min(100, (float)$risk)) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kondisi Produk --}}
                                    <td class="py-4 px-4">
                                        @if($isDry)
                                            <span class="inline-flex px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-bold">
                                                {{ $storageStatus }}
                                            </span>
                                        @elseif(is_numeric($quality))
                                            <div class="w-32">
                                                <div class="flex items-baseline justify-between text-[15px] font-extrabold {{ $qGrad['text'] }} mb-1.5">
                                                    <span>{{ $quality }}</span>
                                                    <span class="text-xs font-semibold text-slate-400">/100</span>
                                                </div>
                                                <div class="w-full h-3 rounded-full bg-emerald-50 overflow-hidden">
                                                    <div class="h-full rounded-full {{ $qGrad['bar'] }}" style="width: {{ max(0, min(100, (float)$quality)) }}%"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400 font-semibold">Tidak diestimasi</span>
                                        @endif
                                    </td>

                                    {{-- Kelayakan Pengiriman --}}
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border text-sm font-extrabold {{ $feas['classes'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $feas['dot'] }}"></span>
                                            {{ $feas['label'] }}
                                        </span>
                                    </td>

                                    {{-- Waktu Cadangan --}}
                                    <td class="py-4 px-4 text-base font-extrabold text-slate-900 whitespace-nowrap">
                                        @if(is_numeric($margin))
                                            {{ number_format((float) $margin, 1) }} <span class="text-sm font-semibold text-slate-500">jam</span>
                                        @else
                                            <span class="text-sm font-semibold text-slate-400">Tidak tersedia</span>
                                        @endif
                                    </td>

                                    {{-- Emisi Karbon --}}
                                    <td class="py-4 px-4 text-base font-extrabold text-slate-900 whitespace-nowrap">
                                        @if(is_numeric($carbon))
                                            {{ number_format((float) $carbon, 2) }} <span class="text-sm font-semibold text-slate-500">kg</span>
                                        @else
                                            <span class="text-sm font-semibold text-slate-400">Tidak tersedia</span>
                                        @endif
                                    </td>

                                    {{-- Kelengkapan Data --}}
                                    <td class="py-4 px-5">
                                        <div class="w-24">
                                            <span class="text-sm font-extrabold text-purple-700">
                                                {{ is_numeric($evidence) ? $evidence . '%' : '—' }}
                                            </span>
                                            <div class="w-full h-2.5 rounded-full bg-purple-100 overflow-hidden mt-1.5">
                                                <div class="h-full rounded-full bg-gradient-to-r from-purple-400 to-indigo-600" style="width: {{ is_numeric($evidence) ? max(0, min(100, (float)$evidence)) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile / Tablet Cards (< lg) --}}
                <div class="lg:hidden space-y-4">
                    @foreach($allOptions as $option)
                        @php
                            $snap = $option['snapshot'];
                            $isPreferred = $option['key'] === $preferredOptionData['key'];
                            $risk = data_get($snap, 'analysis.risk_score');
                            $isDry = data_get($snap, 'analysis.quality_prediction.condition_model_type') === 'storage_stability';
                            $quality = data_get($snap, 'analysis.quality_at_arrival');
                            $storageStatus = data_get($snap, 'analysis.quality_prediction.storage_stability_assessment.status', 'Perlu data kondisi');
                            $feas = $translateFeasibility(data_get($snap, 'route.freshness_feasibility'));
                            $margin = data_get($snap, 'route.transit_margin_hours');
                            $carbon = data_get($snap, 'carbon.estimated_kg');
                        @endphp

                        <div class="rounded-2xl p-5 border shadow-sm {{ $isPreferred ? 'bg-emerald-50/60 border-emerald-300 ring-1 ring-emerald-200' : 'bg-white border-slate-200' }}">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-base leading-snug">{{ $option['display_name'] }}</h3>
                                    @if($isPreferred)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-600 text-white text-xs font-extrabold mt-1.5">
                                            ★ Pilihan Terbaik AI
                                        </span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border text-sm font-extrabold shrink-0 {{ $feas['classes'] }}">
                                    <span class="w-2 h-2 rounded-full {{ $feas['dot'] }}"></span>
                                    {{ $feas['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="p-3.5 rounded-xl bg-white/85 border border-slate-100">
                                    <p class="text-slate-500 font-semibold text-[13px]">Tingkat Risiko</p>
                                    <p class="text-base font-black text-slate-900 mt-1">{{ is_numeric($risk) ? $risk . '/100' : '—' }}</p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-white/85 border border-slate-100">
                                    <p class="text-slate-500 font-semibold text-[13px]">Kondisi Produk</p>
                                    <p class="text-base font-black text-emerald-700 mt-1">
                                        {{ $isDry ? $storageStatus : (is_numeric($quality) ? $quality . '/100' : '—') }}
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-white/85 border border-slate-100">
                                    <p class="text-slate-500 font-semibold text-[13px]">Waktu Cadangan</p>
                                    <p class="text-base font-black text-slate-900 mt-1">
                                        {{ is_numeric($margin) ? number_format((float)$margin, 1) . ' jam' : 'Tidak tersedia' }}
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-white/85 border border-slate-100">
                                    <p class="text-slate-500 font-semibold text-[13px]">Emisi Karbon</p>
                                    <p class="text-base font-black text-slate-900 mt-1">
                                        {{ is_numeric($carbon) ? number_format((float)$carbon, 2) . ' kg' : 'Tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- =====================================================================
                 SECTION D: WHY AI RECOMMENDS THIS SCENARIO (Delta Analysis)
            ===================================================================== --}}
            @if(!empty($baselineComparison))
                @php
                    $dRisk = data_get($delta, 'risk_points');
                    $dQuality = data_get($delta, 'quality_points');
                    $dMargin = data_get($delta, 'transit_margin_hours');
                    $dCarbon = data_get($delta, 'carbon_kg');
                    $bFeas = $translateFeasibility(data_get($baselineComparison, 'baseline_feasibility'))['label'];
                    $sFeas = $translateFeasibility(data_get($baselineComparison, 'scenario_feasibility'))['label'];

                    $deltaCards = [];

                    $feasSame = $bFeas === $sFeas;
                    $deltaCards[] = [
                        'title' => 'Kelayakan Rute',
                        'value' => $feasSame ? "Tetap {$sFeas}" : "{$bFeas} → {$sFeas}",
                        'desc' => $feasSame ? 'Tidak ada perubahan status kelayakan rute.' : 'Terjadi perubahan status kelayakan rute dibanding rencana saat ini.',
                        'tone' => $feasSame ? 'neutral' : ($decisionType === 'recommended_over_current' ? 'good' : 'warn'),
                    ];

                    if ($dRisk !== null) {
                        $tone = $dRisk <= -1 ? 'good' : ($dRisk >= 1 ? 'bad' : 'neutral');
                        $valStr = $dRisk == 0 ? 'Tetap (0 poin)' : (($dRisk > 0 ? '+' : '') . number_format((float)$dRisk, 1) . ' poin');
                        $desc = $dRisk <= -1 ? 'Risiko pengiriman berhasil diturunkan.' : ($dRisk >= 1 ? 'Risiko pengiriman meningkat dibanding rencana saat ini.' : 'Tingkat risiko relatif tidak berubah.');
                        $deltaCards[] = ['title' => 'Perubahan Risiko', 'value' => $valStr, 'desc' => $desc, 'tone' => $tone];
                    }

                    if ($dQuality !== null) {
                        $tone = $dQuality >= 1 ? 'good' : ($dQuality <= -1 ? 'bad' : 'neutral');
                        $valStr = $dQuality == 0 ? 'Tetap (0 poin)' : (($dQuality > 0 ? '+' : '') . number_format((float)$dQuality, 1) . ' poin');
                        $desc = $dQuality >= 1 ? 'Perkiraan kondisi produk saat tiba lebih baik.' : ($dQuality <= -1 ? 'Perkiraan kondisi produk saat tiba menurun.' : 'Kondisi produk saat tiba relatif sama.');
                        $deltaCards[] = ['title' => 'Kondisi Produk', 'value' => $valStr, 'desc' => $desc, 'tone' => $tone];
                    }

                    if ($dMargin !== null) {
                        $tone = $dMargin >= 0.5 ? 'good' : ($dMargin <= -0.5 ? 'bad' : 'neutral');
                        $valStr = $dMargin == 0 ? 'Tetap (0 jam)' : (($dMargin > 0 ? '+' : '') . number_format((float)$dMargin, 1) . ' jam');
                        $desc = $dMargin >= 0.5 ? 'Memberikan ruang waktu pengiriman lebih longgar.' : ($dMargin <= -0.5 ? 'Mengurangi waktu cadangan sebelum batas kesegaran.' : 'Waktu cadangan pengiriman relatif sama.');
                        $deltaCards[] = ['title' => 'Waktu Cadangan', 'value' => $valStr, 'desc' => $desc, 'tone' => $tone];
                    }

                    if ($dCarbon !== null) {
                        $tone = $dCarbon <= -0.01 ? 'good' : ($dCarbon >= 0.01 ? 'warn' : 'neutral');
                        $valStr = $dCarbon == 0 ? 'Tetap (0 kg)' : (($dCarbon > 0 ? '+' : '') . number_format((float)$dCarbon, 2) . ' kg');
                        $desc = $dCarbon <= -0.01 ? 'Lebih ramah lingkungan dengan emisi lebih rendah.' : ($dCarbon >= 0.01 ? 'Menghasilkan perkiraan emisi karbon lebih tinggi.' : 'Perkiraan emisi karbon relatif sama.');
                        $deltaCards[] = ['title' => 'Emisi Karbon', 'value' => $valStr, 'desc' => $desc, 'tone' => $tone];
                    }
                @endphp

                <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-sm">
                    <div class="mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-extrabold uppercase tracking-wider mb-2">
                            Analisis Faktor Keputusan
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                            Kenapa AI Memilih {{ $preferredOptionData['display_name'] }}?
                        </h2>
                        <p class="text-[15px] text-slate-600 mt-1">
                            Perbandingan langsung antara <strong class="text-slate-800">{{ $bestScenarioName }}</strong> terhadap <strong class="text-slate-800">Rencana Saat Ini</strong>:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($deltaCards as $card)
                            @php
                                $toneStyle = match ($card['tone']) {
                                    'good' => 'bg-emerald-50/70 border-emerald-200 text-emerald-900',
                                    'warn' => 'bg-amber-50/70 border-amber-200 text-amber-900',
                                    'bad' => 'bg-rose-50/70 border-rose-200 text-rose-900',
                                    default => 'bg-slate-50 border-slate-200 text-slate-800',
                                };
                                $badgeStyle = match ($card['tone']) {
                                    'good' => 'bg-emerald-600 text-white',
                                    'warn' => 'bg-amber-500 text-white',
                                    'bad' => 'bg-rose-600 text-white',
                                    default => 'bg-slate-200 text-slate-800',
                                };
                            @endphp
                            <div class="rounded-2xl border p-4 sm:p-5 {{ $toneStyle }}">
                                <div class="flex items-center justify-between gap-2 mb-2.5">
                                    <span class="text-sm font-extrabold opacity-85">{{ $card['title'] }}</span>
                                    <span class="px-2.5 py-1 rounded-lg text-[13px] font-black {{ $badgeStyle }}">{{ $card['value'] }}</span>
                                </div>
                                <p class="text-sm leading-relaxed opacity-90 font-medium">
                                    {{ $card['desc'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- =====================================================================
                 SECTION E: VISUAL COMPARISON CHARTS (Dynamic Gradient & Readable Font)
            ===================================================================== --}}
            @if(count($allOptions) > 1)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Chart 1: Risiko vs Kondisi (Skala 0-100) --}}
                    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm">
                        <div class="mb-6">
                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Perbandingan Skor Risiko & Kondisi</h3>
                            <p class="text-sm text-slate-600 mt-1">
                                Skala 0–100 · Risiko makin kecil makin baik · Kondisi makin besar makin baik
                            </p>
                        </div>

                        <div class="space-y-6">
                            @foreach($allOptions as $option)
                                @php
                                    $rVal = data_get($option['snapshot'], 'analysis.risk_score');
                                    $qVal = data_get($option['snapshot'], 'analysis.quality_at_arrival');
                                    $isPref = $option['key'] === $preferredOptionData['key'];
                                    $rGrad = $riskGradient($rVal);
                                    $qGrad = $qualityGradient($qVal);
                                @endphp
                                <div class="space-y-3 {{ !$loop->first ? 'pt-5 border-t border-slate-100' : '' }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-[15px] font-extrabold leading-snug {{ $isPref ? 'text-emerald-700' : 'text-slate-900' }}">
                                            {{ $option['display_name'] }}
                                        </span>
                                        @if($isPref)
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[13px] font-bold">
                                                Pilihan Terbaik
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Bar Risiko --}}
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-bold text-slate-600">Skor Risiko</span>
                                            <span class="text-base font-extrabold {{ $rGrad['text'] }}">
                                                {{ is_numeric($rVal) ? $rVal . '/100' : '—' }}
                                            </span>
                                        </div>
                                        <div class="w-full h-3.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full {{ $rGrad['bar'] }} transition-all duration-300"
                                                 style="width: {{ is_numeric($rVal) ? max(0, min(100, (float)$rVal)) : 0 }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Bar Kondisi --}}
                                    @if(is_numeric($qVal))
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm font-bold text-slate-600">Kondisi Produk</span>
                                                <span class="text-base font-extrabold {{ $qGrad['text'] }}">
                                                    {{ $qVal }}/100
                                                </span>
                                            </div>
                                            <div class="w-full h-3.5 rounded-full bg-emerald-50 overflow-hidden">
                                                <div class="h-full rounded-full {{ $qGrad['bar'] }} transition-all duration-300"
                                                     style="width: {{ max(0, min(100, (float)$qVal)) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Chart 2: Waktu Cadangan & Emisi Karbon (Skala Terpisah) --}}
                    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm space-y-7">
                        {{-- Waktu Cadangan --}}
                        <div>
                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Waktu Cadangan Pengiriman</h3>
                            <p class="text-sm text-slate-600 mt-1">Dalam satuan jam (makin besar makin aman)</p>

                            <div class="space-y-4 mt-5">
                                @foreach($allOptions as $option)
                                    @php
                                        $mVal = data_get($option['snapshot'], 'route.transit_margin_hours');
                                        $pct = (is_numeric($mVal) && $maxMarginAbs > 0) ? min(100, (abs((float)$mVal) / $maxMarginAbs) * 100) : 0;
                                        $mGrad = $marginGradient($mVal, (float) $maxMarginAbs);
                                    @endphp
                                    <div class="space-y-1.5">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <span class="text-sm sm:text-[15px] font-bold text-slate-800 leading-snug">
                                                {{ $option['display_name'] }}
                                            </span>
                                            <span class="text-base font-extrabold shrink-0 {{ $mGrad['text'] }}">
                                                {{ is_numeric($mVal) ? number_format((float)$mVal, 1) . ' jam' : '—' }}
                                            </span>
                                        </div>
                                        <div class="w-full h-3.5 rounded-full bg-blue-50 overflow-hidden">
                                            <div class="h-full rounded-full {{ $mGrad['bar'] }} transition-all duration-300"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        {{-- Emisi Karbon --}}
                        <div>
                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Perkiraan Emisi Karbon</h3>
                            <p class="text-sm text-slate-600 mt-1">Dalam satuan kg CO₂ (makin rendah makin ramah lingkungan)</p>

                            <div class="space-y-4 mt-5">
                                @foreach($allOptions as $option)
                                    @php
                                        $cVal = data_get($option['snapshot'], 'carbon.estimated_kg');
                                        $pct = (is_numeric($cVal) && $maxCarbon > 0) ? min(100, ((float)$cVal / $maxCarbon) * 100) : 0;
                                        $cGrad = $carbonGradient($cVal, (float) $maxCarbon);
                                    @endphp
                                    <div class="space-y-1.5">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <span class="text-sm sm:text-[15px] font-bold text-slate-800 leading-snug">
                                                {{ $option['display_name'] }}
                                            </span>
                                            <span class="text-base font-extrabold shrink-0 {{ $cGrad['text'] }}">
                                                {{ is_numeric($cVal) ? number_format((float)$cVal, 1) . ' kg' : '—' }}
                                            </span>
                                        </div>
                                        <div class="w-full h-3.5 rounded-full bg-teal-50 overflow-hidden">
                                            <div class="h-full rounded-full {{ $cGrad['bar'] }} transition-all duration-300"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =====================================================================
                 METODE & INFORMASI ANALISIS (Provenance)
            ===================================================================== --}}
            <div class="rounded-3xl bg-slate-900 text-white p-6 sm:p-8 shadow-lg border border-slate-800">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="text-[13px] font-extrabold uppercase tracking-wider text-cyan-400 mb-2">
                            Metode Penilaian AgriFlow AI
                        </p>
                        <p class="text-[15px] text-slate-200 leading-relaxed">
                            Rekomendasi disusun secara bertahap dan terukur berdasarkan urutan prioritas:
                            <strong class="text-white">Kelayakan Rute → Tingkat Risiko → Kondisi Produk → Waktu Cadangan → Emisi Karbon</strong>.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3 flex-shrink-0">
                        <div class="px-4 py-3 rounded-2xl bg-white/5 border border-white/10">
                            <p class="text-[13px] uppercase font-bold text-slate-400 tracking-wider">Versi Mesin</p>
                            <p class="text-sm font-extrabold text-white mt-0.5">{{ $comparisonSet->engine_version }}</p>
                        </div>
                        <div class="px-4 py-3 rounded-2xl bg-white/5 border border-white/10">
                            <p class="text-[13px] uppercase font-bold text-slate-400 tracking-wider">Rata-rata Data</p>
                            <p class="text-sm font-extrabold text-emerald-400 mt-0.5">{{ $comparisonSet->evidence_coverage }}%</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>