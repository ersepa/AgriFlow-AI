<x-app-layout>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.06s; }
        .delay-2 { animation-delay: 0.12s; }
        .delay-3 { animation-delay: 0.18s; }

        .agri-card {
            box-shadow: 0 6px 22px -4px rgba(15, 23, 42, 0.08), 0 2px 8px -2px rgba(15, 23, 42, 0.04);
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-subcard {
            box-shadow: 0 2px 6px -1px rgba(15, 23, 42, 0.05);
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(4, 120, 87, 0.09) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .chevron-icon { transform: rotate(180deg); }
    </style>

    @php
        $final = $finalAnalysis ?? [];
        $snapshotShipment = $completionSnapshot['shipment'] ?? [];
        $condition = $final['condition_assessment']
            ?? ($final['quality_prediction']['condition_assessment'] ?? []);
        $conditionStatusRaw = $condition['overall_status'] ?? null;
        $risk = $final['risk_score'] ?? null;
        $riskLevel = $final['risk_severity'] ?? $final['risk_level'] ?? null;
        $riskLabelRaw = $riskLevel === 'Medium' ? 'Moderate' : $riskLevel;
        $readiness = $final['operational_readiness_score'] ?? null;
        $carbon = $final['carbon_kg'] ?? $shipment->carbon_emission;
        $routeScore = $routeDecision['route_score'] ?? null;
        $routeFeasibilityRaw = $routeDecision['freshness_feasibility'] ?? null;
        $quality = $final['quality_at_arrival'] ?? null;
        $qualityStatusRaw = $final['quality_status'] ?? null;
        $modelType = $final['commodity_profile']['quality_model_type'] ?? null;
        $isDry = $modelType === 'storage_stability';

        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatDateIndo = function ($date) use ($bulanIndo): string {
            if (!$date) return 'Waktu tiba tidak tercatat';
            $c = \Carbon\Carbon::parse($date);
            return $c->format('d') . ' ' . ($bulanIndo[(int) $c->format('n')] ?? $c->format('M')) . ' ' . $c->format('Y · H:i');
        };

        $conditionStatusIndo = match ($conditionStatusRaw) {
            'Within available reference inputs' => 'Sesuai Batas Acuan',
            'Outside reference' => 'Di Luar Batas Acuan',
            'Condition evidence unavailable' => 'Data Kondisi Belum Tersedia',
            null, '' => 'Kondisi belum tercatat',
            default => $conditionStatusRaw,
        };

        $routeFeasibilityIndo = match ($routeFeasibilityRaw) {
            'Safe' => 'Rute Aman',
            'Tight' => 'Waktu Cadangan Ketat',
            'Breach' => 'Melewati Batas Kesegaran',
            null, '', 'Unavailable' => 'Belum tercatat',
            default => $routeFeasibilityRaw,
        };

        $qualityStatusIndo = match ($qualityStatusRaw) {
            'Excellent' => 'Sangat Baik',
            'Good' => 'Baik',
            'At Risk' => 'Berisiko',
            'Poor' => 'Kurang Baik',
            'Very Poor' => 'Sangat Kurang',
            null, '', 'Unavailable' => 'Tidak diestimasi',
            default => $qualityStatusRaw,
        };

        $normalizedRisk = strtolower((string) $riskLabelRaw);
        if ($normalizedRisk === 'high' || ($normalizedRisk === '' && $risk !== null && $risk >= 60)) {
            $riskCardBg = 'bg-[#FECACA] border-[#F87171]';
            $riskAccent = 'text-[#991B1B]';
            $riskBarBg = 'bg-[#DC2626]';
            $riskIconBox = 'bg-white/80 text-[#991B1B] border-[#F87171]';
            $riskLabelIndo = 'Risiko Tinggi';
        } elseif ($normalizedRisk === 'moderate' || ($normalizedRisk === '' && $risk !== null && $risk >= 30)) {
            $riskCardBg = 'bg-[#FDE7B0] border-[#FBBF24]';
            $riskAccent = 'text-[#92400E]';
            $riskBarBg = 'bg-[#D97706]';
            $riskIconBox = 'bg-white/80 text-[#92400E] border-[#FBBF24]';
            $riskLabelIndo = 'Risiko Sedang';
        } elseif ($normalizedRisk === 'low' || ($normalizedRisk === '' && $risk !== null)) {
            $riskCardBg = 'bg-[#C7F0DB] border-[#6EE7B7]';
            $riskAccent = 'text-[#047857]';
            $riskBarBg = 'bg-[#047857]';
            $riskIconBox = 'bg-white/80 text-[#047857] border-[#6EE7B7]';
            $riskLabelIndo = 'Risiko Rendah';
        } else {
            $riskCardBg = 'bg-[#E2E8F0] border-[#CBD5E1]';
            $riskAccent = 'text-[#334155]';
            $riskBarBg = 'bg-[#475569]';
            $riskIconBox = 'bg-white/80 text-[#334155] border-[#CBD5E1]';
            $riskLabelIndo = 'Belum tercatat';
        }
    @endphp

    <div class="min-h-screen bg-[#F1F5F9] bg-grid-pattern text-[#0F172A] relative overflow-x-hidden pb-16">
        <div class="absolute -top-36 -left-36 w-[500px] h-[500px] bg-[#C7F0DB]/50 rounded-full blur-[110px] pointer-events-none"></div>
        <div class="absolute top-1/3 -right-36 w-[440px] h-[440px] bg-[#CCFBF1]/45 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto py-8 sm:py-10 px-4 sm:px-6 relative z-10">

            {{-- Navigasi Kembali --}}
            <div class="mb-6 animate-card">
                <a href="{{ route('completed-shipments.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-[#6EE7B7] bg-[#C7F0DB] hover:bg-[#047857] px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-[#047857] hover:text-white shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-[#047857] focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                    </svg>
                    <span>Kembali ke Pengiriman Selesai</span>
                </a>
            </div>

            {{-- Notifikasi Flash --}}
            @foreach(['success' => 'emerald', 'warning' => 'amber', 'info' => 'cyan'] as $flash => $tone)
                @if(session($flash))
                    <div class="mb-6 rounded-2xl border px-5 py-4 text-sm font-bold flex items-center gap-3 shadow-xs
                        {{ $tone === 'emerald' ? 'border-[#6EE7B7] bg-[#C7F0DB] text-[#047857]' : '' }}
                        {{ $tone === 'amber' ? 'border-[#FBBF24] bg-[#FDE7B0] text-[#92400E]' : '' }}
                        {{ $tone === 'cyan' ? 'border-[#5EEAD4] bg-[#CCFBF1] text-[#0F766E]' : '' }}">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0
                            {{ $tone === 'emerald' ? 'bg-[#047857]' : '' }}
                            {{ $tone === 'amber' ? 'bg-[#92400E]' : '' }}
                            {{ $tone === 'cyan' ? 'bg-[#0F766E]' : '' }}">
                        </span>
                        <span>{{ session($flash) }}</span>
                    </div>
                @endif
            @endforeach

            {{-- ================= 1. HEADER RINGKASAN PENGIRIMAN ================= --}}
            <section aria-label="Ringkasan Pengiriman" class="agri-card border border-[#6EE7B7] bg-[#C7F0DB] p-6 sm:p-8 animate-card">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/85 border border-[#6EE7B7] text-[#047857] flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-[#047857]">Laporan Kinerja Pengiriman · #{{ $shipment->id }}</p>
                            <h1 class="text-3xl sm:text-4xl font-black text-[#0F172A] capitalize tracking-tight mt-1">
                                {{ $shipment->harvest?->commodity ?? 'Komoditas tidak diketahui' }}
                            </h1>
                            <div class="inline-flex flex-wrap items-center gap-2 mt-2.5 text-sm font-bold text-[#047857]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span class="capitalize">{{ $shipment->origin }}</span>
                                <span class="font-black text-[#0F172A]" aria-hidden="true">→</span>
                                <span class="capitalize">{{ $shipment->destination }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end gap-2.5 shrink-0">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#34D399] bg-white px-4 py-1.5 text-xs font-extrabold uppercase tracking-wider text-[#047857] shadow-2xs">
                            <svg class="w-4 h-4 text-[#047857]" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                            </svg>
                            <span>Telah Tiba</span>
                        </span>

                        <div class="inline-flex items-center gap-2 rounded-xl bg-white/80 border border-[#6EE7B7] px-3.5 py-2 text-xs font-extrabold text-[#0F172A]">
                            <svg class="w-4 h-4 text-[#047857] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>
                            </svg>
                            <span>{{ $formatDateIndo($shipment->delivered_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-[#6EE7B7] bg-white/80 px-4 py-3.5 flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#047857] shrink-0"></span>
                    <p class="text-sm font-semibold text-[#334155]">
                        Pengiriman ini telah selesai. Hasil akhir perjalanan dan riwayat analisis AI sebelumnya dapat Anda tinjau di bawah ini.
                    </p>
                </div>
            </section>

            {{-- ================= 2. KINERJA AKHIR PENGIRIMAN ================= --}}
            @if($isLegacyCompletion)
                <section aria-label="Catatan Pengiriman Lama" class="mt-6 agri-card border border-[#FBBF24] bg-[#FDE7B0] p-6 animate-card delay-1">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-2xl bg-white/80 border border-[#FBBF24] text-[#92400E] flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-[#92400E]">Arsip Pengiriman Lama</p>
                            <h2 class="text-lg font-black text-[#0F172A] mt-1">Ringkasan skor akhir belum tersedia untuk pengiriman lama ini.</h2>
                            <p class="text-sm font-semibold text-[#92400E] mt-1.5 leading-relaxed">
                                Pengiriman ini diselesaikan sebelum ringkasan akhir disimpan secara otomatis. Riwayat analisis AI dan detail pengiriman tetap tersedia di bawah.
                            </p>
                        </div>
                    </div>
                </section>
            @else
                <section aria-label="Kinerja Akhir Pengiriman" class="mt-6 animate-card delay-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                        {{-- A. Risiko Pengiriman --}}
                        <div class="agri-card border p-5 flex flex-col justify-between {{ $riskCardBg }}">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-extrabold uppercase tracking-wider {{ $riskAccent }}">Risiko Pengiriman</p>
                                <span class="w-9 h-9 rounded-xl border {{ $riskIconBox }} flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="mt-3">
                                <p class="text-3xl font-black text-[#0F172A]">
                                    {{ $risk !== null ? $risk : '—' }}<span class="text-sm font-extrabold {{ $riskAccent }}">/100</span>
                                </p>
                                @if($risk !== null)
                                    <div class="w-full h-2 rounded-full bg-white/80 mt-2.5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $riskBarBg }}" style="width: {{ min(100, max(0, (float) $risk)) }}%"></div>
                                    </div>
                                @endif
                                <p class="text-xs font-extrabold {{ $riskAccent }} mt-2">{{ $riskLabelIndo }}</p>
                            </div>
                        </div>

                        {{-- B. Kesiapan Pengiriman --}}
                        <div class="agri-card bg-[#CCFBF1] border border-[#5EEAD4] p-5 flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-[#0F766E]">Kesiapan Pengiriman</p>
                                <span class="w-9 h-9 rounded-xl bg-white/80 text-[#0F766E] border border-[#5EEAD4] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="mt-3">
                                <p class="text-3xl font-black text-[#0F172A]">
                                    {{ $readiness !== null ? $readiness : '—' }}<span class="text-sm font-extrabold text-[#0F766E]">/100</span>
                                </p>
                                @if($readiness !== null)
                                    <div class="w-full h-2 rounded-full bg-white/80 mt-2.5 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#0F766E]" style="width: {{ min(100, max(0, (float) $readiness)) }}%"></div>
                                    </div>
                                @endif
                                <p class="text-xs font-bold text-[#0F766E] mt-2">Skor kesiapan akhir</p>
                            </div>
                        </div>

                        {{-- C. Skor Rute --}}
                        <div class="agri-card bg-[#C9DFFF] border border-[#93C5FD] p-5 flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-[#1D4ED8]">Skor Rute</p>
                                <span class="w-9 h-9 rounded-xl bg-white/80 text-[#1D4ED8] border border-[#93C5FD] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="mt-3">
                                <p class="text-3xl font-black text-[#0F172A]">
                                    {{ $routeScore !== null ? $routeScore : '—' }}<span class="text-sm font-extrabold text-[#1D4ED8]">/100</span>
                                </p>
                                @if($routeScore !== null)
                                    <div class="w-full h-2 rounded-full bg-white/80 mt-2.5 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#1D4ED8]" style="width: {{ min(100, max(0, (float) $routeScore)) }}%"></div>
                                    </div>
                                @endif
                                <p class="text-xs font-bold text-[#1D4ED8] mt-2">{{ $routeFeasibilityIndo }}</p>
                            </div>
                        </div>

                        {{-- D. Emisi Karbon --}}
                        <div class="agri-card bg-[#E9D5FF] border border-[#C084FC] p-5 flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-[#7E22CE]">Emisi Karbon</p>
                                <span class="w-9 h-9 rounded-xl bg-white/80 text-[#7E22CE] border border-[#C084FC] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="mt-3">
                                <p class="text-3xl font-black text-[#0F172A]">
                                    {{ $carbon !== null ? number_format((float) $carbon, 2, ',', '.') : '—' }}
                                </p>
                                <p class="text-xs font-bold text-[#7E22CE] mt-2">kg CO₂e estimasi angkutan</p>
                            </div>
                        </div>

                    </div>
                </section>

                {{-- ================= 3. KONDISI PENGIRIMAN & KUALITAS SAAT TIBA ================= --}}
                <section aria-label="Kondisi Pengiriman dan Kualitas Saat Tiba" class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6 animate-card delay-2">

                    {{-- Kiri (7 col): Kondisi Pengiriman --}}
                    <div class="lg:col-span-7 agri-card bg-white border border-slate-300/90 p-6">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-[#047857]">Data Pengukuran Tercatat</p>
                                <h2 class="text-xl font-black text-[#0F172A] mt-0.5">Kondisi Pengiriman</h2>
                            </div>
                            <span class="rounded-full bg-[#C7F0DB] border border-[#6EE7B7] px-3 py-1 text-xs font-extrabold text-[#047857]">
                                {{ $isDry ? 'Komoditas Kering' : 'Produk Segar' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @if($isDry)
                                <div class="agri-subcard rounded-2xl bg-[#FDE7B0] border border-[#FBBF24] p-4 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#92400E]">Kadar Air</p>
                                        <p class="text-2xl font-black text-[#0F172A] mt-1">
                                            {{ $shipment->recorded_moisture_percent !== null ? number_format($shipment->recorded_moisture_percent, 1, ',', '.') . '%' : '—' }}
                                        </p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-white/80 text-[#92400E] border border-[#FBBF24] flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>
                                        </svg>
                                    </div>
                                </div>
                            @else
                                <div class="agri-subcard rounded-2xl bg-[#FDE7B0] border border-[#FBBF24] p-4 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#92400E]">Suhu Produk</p>
                                        <p class="text-2xl font-black text-[#0F172A] mt-1">
                                            {{ $shipment->recorded_temperature_c !== null ? number_format($shipment->recorded_temperature_c, 1, ',', '.') . '°C' : '—' }}
                                        </p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-white/80 text-[#92400E] border border-[#FBBF24] flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif

                            {{-- Kelembapan --}}
                            <div class="agri-subcard rounded-2xl bg-[#C9DFFF] border border-[#93C5FD] p-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#1D4ED8]">Kelembapan</p>
                                    <p class="text-2xl font-black text-[#0F172A] mt-1">
                                        {{ $shipment->recorded_relative_humidity_percent !== null ? number_format($shipment->recorded_relative_humidity_percent, 1, ',', '.') . '%' : '—' }}
                                    </p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-white/80 text-[#1D4ED8] border border-[#93C5FD] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Status Kondisi --}}
                            <div class="agri-subcard rounded-2xl bg-[#C7F0DB] border border-[#6EE7B7] p-4 flex items-start gap-3 {{ $shipment->condition_source ? '' : 'sm:col-span-2' }}">
                                <div class="w-9 h-9 rounded-xl bg-white/80 text-[#047857] border border-[#6EE7B7] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#047857]">Status Kondisi</p>
                                    <p class="text-sm font-black text-[#0F172A] mt-1 leading-snug">
                                        {{ $conditionStatusIndo }}
                                    </p>
                                </div>
                            </div>

                            {{-- Sumber Data --}}
                            @if($shipment->condition_source)
                                <div class="agri-subcard rounded-2xl bg-[#E9D5FF] border border-[#C084FC] p-4 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-white/80 text-[#7E22CE] border border-[#C084FC] flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#7E22CE]">Sumber Data</p>
                                        <p class="text-sm font-black text-[#0F172A] mt-1">
                                            {{ $shipment->condition_source === 'manual_entry' ? 'Input Manual' : $shipment->condition_source }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Kanan (5 col): Kualitas Produk Saat Tiba --}}
                    <div class="lg:col-span-5 agri-card bg-[#CCFBF1] border border-[#5EEAD4] p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-[#0F766E]">Hasil Pengiriman</p>
                                    <h2 class="text-xl font-black text-[#0F172A] mt-0.5">Kondisi Produk Saat Tiba</h2>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-white/85 text-[#0F766E] border border-[#5EEAD4] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
                                    </svg>
                                </div>
                            </div>

                            @if($isDry)
                                <div class="mt-5 rounded-2xl bg-white/85 border border-[#5EEAD4] p-4">
                                    <p class="text-sm font-black text-[#0F172A]">Dinilai Melalui Stabilitas Penyimpanan</p>
                                    <p class="text-sm font-semibold text-[#334155] mt-1.5 leading-relaxed">
                                        Komoditas kering ini dinilai menggunakan standar stabilitas penyimpanan, bukan skor kesegaran produk segar.
                                    </p>
                                </div>
                            @else
                                <div class="mt-5 rounded-2xl bg-white/85 border border-[#5EEAD4] p-5">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <p class="text-4xl font-black text-[#0F172A]">
                                            {{ $quality !== null ? $quality : '—' }}@if($quality !== null)<span class="text-base font-extrabold text-[#0F766E]">/100</span>@endif
                                        </p>
                                        <span class="rounded-full bg-[#CCFBF1] border border-[#5EEAD4] px-3 py-1 text-xs font-extrabold text-[#0F766E]">
                                            {{ $qualityStatusIndo }}
                                        </span>
                                    </div>

                                    @if($quality !== null && is_numeric($quality))
                                        <div class="w-full h-3 rounded-full bg-[#E2E8F0] mt-4 overflow-hidden">
                                            <div class="h-full rounded-full bg-[#0F766E]" style="width: {{ min(100, max(0, (float) $quality)) }}%"></div>
                                        </div>
                                    @endif

                                    <p class="text-xs font-semibold text-[#334155] mt-3">
                                        Perkiraan tingkat kesegaran produk saat tiba di {{ $shipment->destination }}.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                </section>
            @endif

            {{-- ================= 4. RIWAYAT ANALISIS AI SEBELUMNYA ================= --}}
            <section aria-label="Riwayat Analisis AI" class="mt-6 agri-card bg-white border border-slate-300/90 p-6 sm:p-7 animate-card delay-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E9D5FF] border border-[#C084FC] text-[#7E22CE] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-[#0F172A]">Riwayat Analisis AI</h2>
                            <p class="text-xs font-semibold text-[#334155]">Catatan evaluasi risiko yang pernah tersimpan untuk pengiriman ini</p>
                        </div>
                    </div>

                    <a href="{{ route('ai-analysis.history') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-[#E9D5FF] hover:bg-[#7E22CE] border border-[#C084FC] px-3.5 py-2 text-xs font-extrabold uppercase tracking-wider text-[#7E22CE] hover:text-white transition-all self-start sm:self-auto">
                        <span>Lihat Semua Riwayat</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($shipment->aiAnalyses as $record)
                        @php
                            $recLevelRaw = $record->risk_level === 'Medium' ? 'Moderate' : $record->risk_level;
                            $recTone = match (strtolower((string) $recLevelRaw)) {
                                'high' => [
                                    'label' => 'Risiko Tinggi',
                                    'row' => 'bg-[#FECACA]/75 border-[#F87171] hover:bg-[#FECACA]',
                                    'badge' => 'bg-[#991B1B] text-white',
                                    'text' => 'text-[#991B1B]',
                                ],
                                'moderate' => [
                                    'label' => 'Risiko Sedang',
                                    'row' => 'bg-[#FDE7B0]/75 border-[#FBBF24] hover:bg-[#FDE7B0]',
                                    'badge' => 'bg-[#92400E] text-white',
                                    'text' => 'text-[#92400E]',
                                ],
                                default => [
                                    'label' => 'Risiko Rendah',
                                    'row' => 'bg-[#C7F0DB]/75 border-[#6EE7B7] hover:bg-[#C7F0DB]',
                                    'badge' => 'bg-[#047857] text-white',
                                    'text' => 'text-[#047857]',
                                ],
                            };
                        @endphp

                        <a href="{{ route('ai-analysis.show', $record) }}"
                           class="agri-subcard flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border px-4 py-3.5 transition-all {{ $recTone['row'] }}">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider {{ $recTone['badge'] }}">
                                    {{ $recTone['label'] }}
                                </span>
                                <div>
                                    <p class="text-sm font-black text-[#0F172A]">
                                        Skor Risiko: {{ str_replace('/100', '', (string) $record->waste_probability) }}/100
                                    </p>
                                    <p class="text-xs font-bold {{ $recTone['text'] }}">
                                        {{ $formatDateIndo($record->created_at) }}
                                    </p>
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/90 border border-slate-300 px-3.5 py-2 text-xs font-extrabold uppercase tracking-wider text-[#0F172A] self-start sm:self-center shadow-2xs">
                                <span>Lihat Analisis</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-[#F1F5F9] border border-slate-300 p-5 text-center">
                            <p class="text-sm font-bold text-[#334155]">Belum ada riwayat analisis AI yang tersimpan untuk pengiriman ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- ================= 5. DETAIL TAMBAHAN PENGIRIMAN (Collapsible) ================= --}}
            <section aria-label="Detail Tambahan Pengiriman" class="mt-6 animate-card delay-3">
                <details class="agri-card bg-white border border-slate-300/90 overflow-hidden">
                    <summary class="cursor-pointer px-6 py-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors select-none">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#C9DFFF] border border-[#93C5FD] text-[#1D4ED8] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-[#0F172A]">Detail Tambahan Pengiriman</h2>
                                <p class="text-xs font-semibold text-[#334155]">Berat muatan, jarak perjalanan, estimasi waktu, dan versi rekaman</p>
                            </div>
                        </div>

                        <span class="chevron-icon w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-[#0F172A] flex items-center justify-center transition-transform duration-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </span>
                    </summary>

                    <div class="px-6 pb-6 pt-2 border-t border-slate-200">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mt-3">
                            <div class="rounded-2xl bg-[#C7F0DB] border border-[#6EE7B7] p-4">
                                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-[#047857]">Berat Muatan</dt>
                                <dd class="text-lg font-black text-[#0F172A] mt-1">
                                    {{ $shipment->harvest?->weight !== null ? number_format((float) $shipment->harvest->weight, 0, ',', '.') . ' kg' : 'Tidak tercatat' }}
                                </dd>
                            </div>

                            <div class="rounded-2xl bg-[#C9DFFF] border border-[#93C5FD] p-4">
                                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-[#1D4ED8]">Jarak Perjalanan</dt>
                                <dd class="text-lg font-black text-[#0F172A] mt-1">
                                    {{ $shipment->distance_km !== null ? number_format((float) $shipment->distance_km, 1, ',', '.') . ' km' : 'Tidak tercatat' }}
                                </dd>
                            </div>

                            <div class="rounded-2xl bg-[#CCFBF1] border border-[#5EEAD4] p-4">
                                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-[#0F766E]">Estimasi Waktu</dt>
                                <dd class="text-lg font-black text-[#0F172A] mt-1">
                                    {{ $shipment->duration_hours !== null ? number_format((float) $shipment->duration_hours, 1, ',', '.') . ' jam' : 'Tidak tercatat' }}
                                </dd>
                            </div>

                            <div class="rounded-2xl bg-[#E9D5FF] border border-[#C084FC] p-4">
                                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-[#7E22CE]">Versi Rekaman</dt>
                                <dd class="text-lg font-black text-[#0F172A] mt-1">
                                    {{ $completionSnapshot['snapshot_version'] ?? 'Arsip Lama' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </details>
            </section>

        </div>
    </div>
</x-app-layout>