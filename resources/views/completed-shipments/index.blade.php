<x-app-layout>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.06s; }
        .delay-2 { animation-delay: 0.12s; }

        .agri-card {
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.025);
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -6px rgba(15, 23, 42, 0.09), 0 6px 14px -2px rgba(15, 23, 42, 0.04);
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    @php
        $totalCompleted = $completedSummary['total']
            ?? (method_exists($shipments, 'total') ? $shipments->total() : $shipments->count());

        $avgFinalRisk = $completedSummary['avg_risk'] ?? null;
        $avgFinalReadiness = $completedSummary['avg_readiness'] ?? null;
        $snapshotCount = $completedSummary['snapshot_count'] ?? null;

        if (!isset($completedSummary) && (!method_exists($shipments, 'hasPages') || !$shipments->hasPages())) {
            $riskScores = [];
            $readinessScores = [];

            foreach ($shipments as $item) {
                $itemFinal = ($item->completion_snapshot ?? [])['analysis'] ?? [];
                if (isset($itemFinal['risk_score']) && is_numeric($itemFinal['risk_score'])) {
                    $riskScores[] = (float) $itemFinal['risk_score'];
                }
                if (isset($itemFinal['operational_readiness_score']) && is_numeric($itemFinal['operational_readiness_score'])) {
                    $readinessScores[] = (float) $itemFinal['operational_readiness_score'];
                }
            }

            $avgFinalRisk = count($riskScores) > 0 ? round(array_sum($riskScores) / count($riskScores), 1) : null;
            $avgFinalReadiness = count($readinessScores) > 0 ? round(array_sum($readinessScores) / count($readinessScores), 1) : null;
            $snapshotCount = max(count($riskScores), count($readinessScores));
        }

        $avgRiskTone = match (true) {
            $avgFinalRisk === null => 'slate',
            $avgFinalRisk >= 60 => 'red',
            $avgFinalRisk >= 30 => 'amber',
            default => 'green',
        };

        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatDateIndo = function ($date) use ($bulanIndo): string {
            if (!$date) return 'Tidak tercatat (pengiriman lama)';
            $c = \Carbon\Carbon::parse($date);
            return $c->format('d') . ' ' . ($bulanIndo[(int) $c->format('n')] ?? $c->format('M')) . ' ' . $c->format('Y · H:i');
        };

        $translateCondition = function (?string $status): string {
            return match ($status) {
                'Within available reference inputs' => 'Sesuai Batas Acuan',
                'Outside reference' => 'Di Luar Batas Acuan',
                'Condition evidence unavailable' => 'Data Kondisi Belum Tersedia',
                null, '' => 'Kondisi akhir belum tercatat',
                default => $status,
            };
        };
    @endphp

    <div class="min-h-screen bg-[#F8FAFC] bg-grid-pattern text-[#0F172A] relative overflow-x-hidden pb-16">
        <div class="absolute -top-36 -left-36 w-[520px] h-[520px] bg-[#D1FAE5]/45 rounded-full blur-[110px] pointer-events-none"></div>
        <div class="absolute top-1/3 -right-36 w-[460px] h-[460px] bg-[#CCFBF1]/40 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 relative z-10">

            {{-- ================= 1. HEADER HALAMAN ================= --}}
            <header class="mb-8 animate-card">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-emerald-200 bg-emerald-50 text-[#065F46] text-xs font-bold tracking-wide shadow-2xs">
                            <svg class="w-4 h-4 text-[#059669]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/>
                            </svg>
                            <span>Riwayat Pengiriman</span>
                            <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl font-black text-[#0F172A] tracking-tight mt-3">
                            Pengiriman <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#059669] to-[#0D9488]">Selesai</span>
                        </h1>

                        <p class="text-sm sm:text-base text-[#64748B] font-medium mt-2 max-w-2xl leading-relaxed">
                            Tinjau seluruh pengiriman yang telah tiba di tujuan beserta hasil akhir risiko, kesiapan, dan kondisi produknya.
                        </p>
                    </div>

                    <a href="{{ route('shipments.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 hover:bg-[#059669] px-5 py-3 text-xs font-extrabold uppercase tracking-wider text-[#065F46] hover:text-white shadow-2xs transition-all self-start md:self-auto focus:outline-none focus:ring-2 focus:ring-[#059669] focus:ring-offset-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        <span>Pengiriman Aktif</span>
                    </a>
                </div>
            </header>

            {{-- ================= NOTIFIKASI FLASH ================= --}}
            @foreach(['success' => 'emerald', 'warning' => 'amber', 'info' => 'cyan'] as $flash => $tone)
                @if(session($flash))
                    <div class="mb-6 rounded-2xl border px-5 py-4 text-sm font-bold flex items-center gap-3 shadow-2xs
                        {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : '' }}
                        {{ $tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-900' : '' }}
                        {{ $tone === 'cyan' ? 'border-cyan-200 bg-cyan-50 text-cyan-900' : '' }}">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0
                            {{ $tone === 'emerald' ? 'bg-[#059669]' : '' }}
                            {{ $tone === 'amber' ? 'bg-[#D97706]' : '' }}
                            {{ $tone === 'cyan' ? 'bg-[#0891B2]' : '' }}">
                        </span>
                        <span>{{ session($flash) }}</span>
                    </div>
                @endif
            @endforeach

            {{-- ================= 2. RINGKASAN STATISTIK ================= --}}
            @if($totalCompleted > 0)
                <section aria-label="Ringkasan Pengiriman Selesai" class="mb-8 animate-card delay-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 {{ ($avgFinalRisk !== null || $avgFinalReadiness !== null) ? 'lg:grid-cols-3' : 'lg:grid-cols-1 max-w-sm' }} gap-4">

                        {{-- Total Pengiriman Tiba --}}
                        <div class="agri-card p-5 bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-900">Total Pengiriman Tiba</p>
                                <p class="text-3xl font-black text-[#059669] mt-1.5">{{ $totalCompleted }}</p>
                                <p class="text-xs font-semibold text-emerald-800/80 mt-1">Pengiriman selesai diarsipkan</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Rata-rata Risiko Akhir --}}
                        @if($avgFinalRisk !== null)
                            @php
                                $avgRiskCardClasses = match ($avgRiskTone) {
                                    'red' => 'bg-red-50 border-red-200',
                                    'amber' => 'bg-amber-50 border-amber-200',
                                    default => 'bg-green-50 border-green-200',
                                };
                                $avgRiskTitleClasses = match ($avgRiskTone) {
                                    'red' => 'text-red-900',
                                    'amber' => 'text-amber-900',
                                    default => 'text-green-900',
                                };
                                $avgRiskValueClasses = match ($avgRiskTone) {
                                    'red' => 'text-[#DC2626]',
                                    'amber' => 'text-[#D97706]',
                                    default => 'text-[#059669]',
                                };
                                $avgRiskSubClasses = match ($avgRiskTone) {
                                    'red' => 'text-red-800/80',
                                    'amber' => 'text-amber-800/80',
                                    default => 'text-green-800/80',
                                };
                                $avgRiskIconClasses = match ($avgRiskTone) {
                                    'red' => 'bg-red-100 text-[#DC2626] border-red-200',
                                    'amber' => 'bg-amber-100 text-[#D97706] border-amber-200',
                                    default => 'bg-green-100 text-[#059669] border-green-200',
                                };
                            @endphp

                            <div class="agri-card p-5 border {{ $avgRiskCardClasses }} flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider {{ $avgRiskTitleClasses }}">Rata-rata Risiko Akhir</p>
                                    <div class="flex items-baseline gap-1 mt-1.5">
                                        <p class="text-3xl font-black {{ $avgRiskValueClasses }}">{{ number_format((float) $avgFinalRisk, 1, ',', '.') }}</p>
                                        <span class="text-sm font-bold {{ $avgRiskSubClasses }}">/100</span>
                                    </div>
                                    <p class="text-xs font-semibold {{ $avgRiskSubClasses }} mt-1">
                                        Skor lebih rendah berarti perjalanan lebih aman
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-2xl border {{ $avgRiskIconClasses }} flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>
                            </div>
                        @endif

                        {{-- Rata-rata Kesiapan Akhir --}}
                        @if($avgFinalReadiness !== null)
                            <div class="agri-card p-5 bg-teal-50 border border-teal-200 flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-teal-900">Rata-rata Kesiapan Akhir</p>
                                    <div class="flex items-baseline gap-1 mt-1.5">
                                        <p class="text-3xl font-black text-[#0D9488]">{{ number_format((float) $avgFinalReadiness, 1, ',', '.') }}</p>
                                        <span class="text-sm font-bold text-teal-800/80">/100</span>
                                    </div>
                                    <p class="text-xs font-semibold text-teal-800/80 mt-1">
                                        Dari {{ $snapshotCount }} pengiriman yang memiliki rekaman akhir
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                    </svg>
                                </div>
                            </div>
                        @endif

                    </div>
                </section>
            @endif

            {{-- ================= PENCARIAN & FILTER RINGAN ================= --}}
            @if($shipments->isNotEmpty())
                <div class="mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white/90 border border-slate-200/90 rounded-2xl p-4 shadow-2xs animate-card delay-1">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                            </svg>
                        </span>
                        <input type="text"
                               id="completedSearchInput"
                               placeholder="Cari komoditas, asal, tujuan, atau ID..."
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <select id="completedRiskFilter"
                            class="w-full sm:w-auto rounded-xl bg-slate-50 border border-slate-200 py-2.5 pl-3.5 pr-9 text-sm font-bold text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="all">Semua Tingkat Risiko</option>
                        <option value="low">Risiko Rendah</option>
                        <option value="moderate">Risiko Sedang</option>
                        <option value="high">Risiko Tinggi</option>
                        <option value="legacy">Data Lama</option>
                    </select>
                </div>
            @endif

            {{-- ================= 3. DAFTAR KARTU PENGIRIMAN SELESAI ================= --}}
            <section aria-label="Daftar Pengiriman Selesai" class="animate-card delay-2">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($shipments as $shipment)
                        @php
                            $snapshot = $shipment->completion_snapshot ?? [];
                            $final = $snapshot['analysis'] ?? [];
                            $risk = $final['risk_score'] ?? null;
                            $readiness = $final['operational_readiness_score'] ?? null;
                            $condition = $final['condition_assessment']['overall_status']
                                ?? ($final['quality_prediction']['condition_assessment']['overall_status'] ?? null);
                            $riskLevel = $final['risk_severity'] ?? $final['risk_level'] ?? null;
                            $riskLabelRaw = $riskLevel === 'Medium' ? 'Moderate' : $riskLevel;

                            $normalizedRisk = strtolower((string) $riskLabelRaw);
                            if (empty($snapshot)) {
                                $riskFilterKey = 'legacy';
                            } elseif ($normalizedRisk === 'high' || ($normalizedRisk === '' && $risk !== null && $risk >= 60)) {
                                $riskFilterKey = 'high';
                            } elseif ($normalizedRisk === 'moderate' || ($normalizedRisk === '' && $risk !== null && $risk >= 30)) {
                                $riskFilterKey = 'moderate';
                            } else {
                                $riskFilterKey = 'low';
                            }

                            if ($riskFilterKey === 'high') {
                                $riskCardClass = 'bg-red-50 border-red-200';
                                $riskLabelClass = 'text-red-900';
                                $riskValueClass = 'text-[#DC2626]';
                                $riskSubClass = 'text-red-800/85';
                                $riskIconClass = 'bg-red-100 text-[#DC2626] border-red-200';
                                $riskLabelIndo = 'Risiko Tinggi';
                            } elseif ($riskFilterKey === 'moderate') {
                                $riskCardClass = 'bg-amber-50 border-amber-200';
                                $riskLabelClass = 'text-amber-900';
                                $riskValueClass = 'text-[#D97706]';
                                $riskSubClass = 'text-amber-800/85';
                                $riskIconClass = 'bg-amber-100 text-[#D97706] border-amber-200';
                                $riskLabelIndo = 'Risiko Sedang';
                            } elseif ($riskFilterKey === 'low' && !empty($snapshot)) {
                                $riskCardClass = 'bg-green-50 border-green-200';
                                $riskLabelClass = 'text-green-900';
                                $riskValueClass = 'text-[#059669]';
                                $riskSubClass = 'text-green-800/85';
                                $riskIconClass = 'bg-green-100 text-[#059669] border-green-200';
                                $riskLabelIndo = 'Risiko Rendah';
                            } else {
                                $riskCardClass = 'bg-slate-50 border-slate-200';
                                $riskLabelClass = 'text-slate-700';
                                $riskValueClass = 'text-slate-800';
                                $riskSubClass = 'text-slate-500';
                                $riskIconClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                $riskLabelIndo = 'Belum tercatat';
                            }

                            $commodityName = $shipment->harvest?->commodity ?? 'Komoditas tidak diketahui';
                        @endphp

                        <article class="completed-card agri-card agri-card-hover bg-white border border-slate-200/90 p-5 sm:p-6 flex flex-col justify-between gap-4"
                                 data-search="{{ strtolower($commodityName . ' ' . $shipment->origin . ' ' . $shipment->destination . ' #' . $shipment->id) }}"
                                 data-risk="{{ $riskFilterKey }}">
                            <div class="space-y-3.5">

                                {{-- A. Identitas Pengiriman (Soft Emerald) --}}
                                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-700">
                                                    <span>#{{ $shipment->id }}</span>
                                                    @if($shipment->harvest?->weight !== null)
                                                        <span>•</span>
                                                        <span>{{ number_format((float) $shipment->harvest->weight, 0, ',', '.') }} kg</span>
                                                    @endif
                                                </div>
                                                <h2 class="text-lg font-black text-emerald-950 capitalize truncate">
                                                    {{ $commodityName }}
                                                </h2>
                                                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-800/90 mt-1">
                                                    <svg class="w-3.5 h-3.5 text-[#0D9488] shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                                                    </svg>
                                                    <span class="capitalize truncate">{{ $shipment->origin }}</span>
                                                    <span class="text-[#059669] font-black" aria-hidden="true">→</span>
                                                    <span class="capitalize truncate">{{ $shipment->destination }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-white/90 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#059669] shrink-0 shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                                            </svg>
                                            <span>Telah Tiba</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- B. Tanggal Penyelesaian (Soft Blue) --}}
                                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-3.5 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] uppercase font-extrabold tracking-wider text-blue-800">Tanggal Penyelesaian</p>
                                        <p class="text-sm font-black text-blue-950 mt-0.5">
                                            {{ $formatDateIndo($shipment->delivered_at) }}
                                        </p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-[#2563EB] border border-blue-200 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>
                                        </svg>
                                    </div>
                                </div>

                                @if(!empty($snapshot))
                                    {{-- C. Kinerja Akhir Pengiriman --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl border p-3.5 flex flex-col justify-between {{ $riskCardClass }}">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <p class="text-[10px] uppercase font-extrabold tracking-wider {{ $riskLabelClass }}">Risiko Akhir</p>
                                                <span class="w-7 h-7 rounded-lg border {{ $riskIconClass }} flex items-center justify-center shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-xl font-black {{ $riskValueClass }}">{{ $risk !== null ? $risk . '/100' : '—' }}</p>
                                                <p class="text-[11px] font-bold {{ $riskSubClass }} mt-0.5">{{ $riskLabelIndo }}</p>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-teal-50 border border-teal-200 p-3.5 flex flex-col justify-between">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <p class="text-[10px] uppercase font-extrabold tracking-wider text-teal-900">Kesiapan Akhir</p>
                                                <span class="w-7 h-7 rounded-lg bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-xl font-black text-[#0D9488]">{{ $readiness !== null ? $readiness . '/100' : '—' }}</p>
                                                <p class="text-[11px] font-semibold text-teal-800/85 mt-0.5">Skor kesiapan akhir</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- D. Kondisi Produk Saat Tiba --}}
                                    <div class="rounded-2xl bg-purple-50 border border-purple-200 p-3.5 flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-[#9333EA] border border-purple-200 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] uppercase font-extrabold tracking-wider text-purple-900">Kondisi Produk Saat Tiba</p>
                                            <p class="text-sm font-bold text-purple-950 mt-0.5 leading-snug">
                                                {{ $translateCondition($condition) }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    {{-- Catatan Pengiriman Lama --}}
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-extrabold text-amber-900">Data analisis akhir belum tersedia</p>
                                            <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                                                Pengiriman ini diselesaikan sebelum skor risiko dan kesiapan akhir disimpan secara otomatis.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- E. Tombol Lihat Detail --}}
                            <a href="{{ route('completed-shipments.show', $shipment) }}"
                               class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#0F172A] hover:bg-[#059669] px-5 py-3 text-xs font-extrabold uppercase tracking-wider text-white transition-all shadow-xs focus:outline-none focus:ring-2 focus:ring-[#059669] focus:ring-offset-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4.268 21a2 2 0 0 0 1.727 1H18a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/><path d="m9 18-1.5-1.5"/><circle cx="5" cy="14" r="3"/>
                                </svg>
                                <span>Lihat Detail</span>
                            </a>
                        </article>
                    @empty
                        {{-- EMPTY STATE --}}
                        <div class="md:col-span-2 xl:col-span-3 agri-card border border-dashed border-emerald-300 bg-emerald-50/60 p-10 sm:p-14 text-center">
                            <div class="mx-auto w-16 h-16 rounded-2xl bg-emerald-100 border border-emerald-200 text-[#059669] flex items-center justify-center shadow-xs">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-black text-emerald-950 mt-4">Belum ada pengiriman yang selesai</h2>
                            <p class="text-sm text-emerald-900/80 mt-2 max-w-md mx-auto leading-relaxed">
                                Pengiriman yang telah tiba di tujuan akan diarsipkan di sini secara otomatis agar mudah Anda tinjau kembali.
                            </p>
                            <a href="{{ route('shipments.index') }}"
                               class="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#059669] hover:bg-[#047857] px-6 py-3 text-xs font-extrabold uppercase tracking-wider text-white transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-[#059669] focus:ring-offset-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                                </svg>
                                <span>Lihat Pengiriman Aktif</span>
                            </a>
                        </div>
                    @endforelse
                </div>

                {{-- Empty State Hasil Filter --}}
                <div id="completedFilterEmpty" class="hidden mt-6 rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center">
                    <p class="text-sm font-bold text-slate-700">Tidak ada pengiriman selesai yang cocok dengan pencarian Anda.</p>
                    <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci atau pilih filter risiko lain.</p>
                </div>
            </section>

            {{-- ================= PAGINATION ================= --}}
            @if($shipments->hasPages())
                <div class="mt-8">{{ $shipments->links() }}</div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('completedSearchInput');
            const riskFilter = document.getElementById('completedRiskFilter');
            const cards = document.querySelectorAll('.completed-card');
            const emptyFilterEl = document.getElementById('completedFilterEmpty');

            if (!searchInput || !riskFilter || cards.length === 0) return;

            function applyFilter() {
                const keyword = searchInput.value.trim().toLowerCase();
                const risk = riskFilter.value;
                let visibleCount = 0;

                cards.forEach(card => {
                    const text = card.dataset.search || '';
                    const cardRisk = card.dataset.risk || '';
                    const matchKeyword = !keyword || text.includes(keyword);
                    const matchRisk = risk === 'all' || cardRisk === risk;

                    if (matchKeyword && matchRisk) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (emptyFilterEl) {
                    emptyFilterEl.classList.toggle('hidden', visibleCount > 0);
                }
            }

            searchInput.addEventListener('input', applyFilter);
            riskFilter.addEventListener('change', applyFilter);
        });
    </script>
</x-app-layout>