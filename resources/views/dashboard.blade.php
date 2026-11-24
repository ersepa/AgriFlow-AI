<x-app-layout>
    {{-- AgriFlow Green & Semantic SaaS Design System --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes floating { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
        @keyframes earthRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .animate-card { animation: fadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.06s; }
        .delay-2 { animation-delay: 0.12s; }
        .delay-3 { animation-delay: 0.18s; }

        [x-cloak] { display: none !important; }

        /* Premium SaaS Card Base */
        .agri-card {
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.025);
            border-radius: 1.25rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -6px rgba(15, 23, 42, 0.09), 0 6px 14px -2px rgba(15, 23, 42, 0.04);
        }

        .agri-card-hover:hover .icon-badge {
            transform: scale(1.08);
        }

        .icon-badge {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Custom Scrollbars */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #F1F5F9; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        /* Animated Orb / Earth */
        .earth { animation: floating 6s ease-in-out infinite, earthRotate 35s linear infinite; }

        /* Weather Forecast Interactive Tabs */
        .weather-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 8px 16px;
            border-radius: 9999px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #475569;
            transition: all 0.2s ease;
            font-weight: 700;
            font-size: 0.8125rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .weather-tab:hover {
            transform: translateY(-1px);
            border-color: #059669;
            color: #065F46;
            background: #ECFDF5;
        }

        .weather-tab:focus-visible {
            outline: 2px solid #059669;
            outline-offset: 2px;
        }

        .weather-tab.active {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #FFFFFF;
            border-color: transparent;
            box-shadow: 0 6px 14px -3px rgba(5, 150, 105, 0.38);
        }

        /* Subtle AgriFlow Dot Grid Pattern */
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen bg-[#F8FAFC] bg-grid-pattern text-[#0F172A] relative overflow-x-hidden pb-16">

        {{-- Soft AgriFlow Ambient Background Glows --}}
        <div class="absolute -top-36 -left-36 w-[520px] h-[520px] bg-[#D1FAE5]/45 rounded-full blur-[110px] pointer-events-none"></div>
        <div class="absolute top-1/3 -right-36 w-[480px] h-[480px] bg-[#CCFBF1]/45 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-12 left-1/4 w-[460px] h-[460px] bg-[#ECFDF5]/60 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 relative z-10">

            {{-- ================================================================= --}}
            {{-- 1. HEADER DASHBOARD & RINGKASAN OPERASIONAL                       --}}
            {{-- ================================================================= --}}
            <header class="mb-8 animate-card">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">

                    {{-- Kiri: Judul & Deskripsi Ringkas --}}
                    <div class="lg:col-span-7">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-[#065F46] text-xs font-bold tracking-wide mb-4 shadow-sm">
                            <svg class="w-4 h-4 text-[#059669]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                            </svg>
                            <span>AgriFlow AI · Logistik Pertanian Cerdas</span>
                            <span class="w-2 h-2 rounded-full bg-[#059669] animate-pulse"></span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-black text-[#0F172A] tracking-tight leading-[1.12]">
                            Pusat Kendali <br class="hidden sm:inline" />
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#059669] via-[#047857] to-[#0D9488]">
                                Logistik Pertanian
                            </span>
                        </h1>

                        <p class="text-[#64748B] mt-3 text-base sm:text-lg font-medium max-w-xl leading-relaxed">
                            Ringkasan operasional distribusi hasil panen Anda. Pantau pengiriman aktif, cuaca rute, dan tingkat risiko dalam satu tampilan.
                        </p>
                    </div>

                    {{-- Kanan: Kartu Sorotan Operasional --}}
                    <div class="lg:col-span-5">
                        <div class="agri-card p-5 sm:p-6 bg-white/95 border border-emerald-200/90 shadow-md space-y-3.5">

                            {{-- 1. Status Operasional Keseluruhan --}}
                            <div class="p-3.5 rounded-2xl border flex items-center justify-between gap-3 {{ $highRisk > 0 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl {{ $highRisk > 0 ? 'bg-amber-100 text-[#D97706] border-amber-300' : 'bg-emerald-100 text-[#059669] border-emerald-300' }} border flex items-center justify-center shrink-0 shadow-xs">
                                        @if($highRisk > 0)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider {{ $highRisk > 0 ? 'text-amber-800/80' : 'text-emerald-800/80' }}">Status Operasional</p>
                                        <h2 class="text-base font-black mt-0.5">
                                            @if($highRisk > 0)
                                                <span class="text-[#D97706]">Perlu Perhatian</span>
                                            @else
                                                <span class="text-[#059669]">Berjalan Lancar</span>
                                            @endif
                                        </h2>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-3 py-1 bg-white/90 text-[#065F46] rounded-full border border-emerald-200 shrink-0 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#059669] animate-pulse"></span>
                                    Status Aktif
                                </span>
                            </div>

                            {{-- 2. Rata-rata Kesiapan Pengiriman Aktif --}}
                            <div class="p-3.5 rounded-2xl bg-emerald-50/85 border border-emerald-200">
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="font-bold text-emerald-950 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#059669]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                        </svg>
                                        Rata-rata kesiapan pengiriman aktif
                                    </span>
                                    <span class="text-base font-black text-[#059669]">{{ round($avgScore) }}%</span>
                                </div>
                                <div class="w-full bg-white rounded-full h-2.5 overflow-hidden border border-emerald-200/80">
                                    <div class="bg-gradient-to-r from-[#059669] to-[#0D9488] h-full rounded-full transition-all duration-700" style="width: {{ min(100, max(0, $avgScore)) }}%"></div>
                                </div>
                            </div>

                            {{-- 3 & 4. Pengiriman Aktif & Indeks Kesiapan --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-teal-50 rounded-xl p-3.5 border border-teal-200 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-teal-800">Pengiriman Aktif</p>
                                        <p class="text-sm font-black text-teal-950 mt-0.5">{{ $activeShipmentCount }} <span class="text-xs font-semibold text-teal-700">aktif</span></p>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="bg-[#065F46] text-white rounded-xl p-3.5 border border-[#047857] flex items-center justify-between shadow-sm" title="Dihitung dari 100 dikurangi Indeks Risiko Operasional">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#A7F3D0]">Indeks Kesiapan</p>
                                        <p class="text-sm font-black text-white mt-0.5">{{ $greenImpactScore }}<span class="text-xs font-semibold text-[#A7F3D0]">/100</span></p>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg bg-white/15 text-[#A7F3D0] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="16" y="16" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M5 16v-3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3"/><path d="M12 12V8"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </header>


            {{-- ================================================================= --}}
            {{-- 2. KARTU STATISTIK UTAMA (6 Kartu Semantik Berwarna)              --}}
            {{-- ================================================================= --}}
            <section aria-label="Statistik Utama" class="mb-8 animate-card delay-1">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @php
                        $items = [
                            [
                                'key' => 'Total Harvest',
                                'label' => 'Total Hasil Panen',
                                'value' => $totalHarvests,
                                'sub' => 'Batch tercatat',
                                'cardBg' => 'bg-emerald-50 border border-emerald-200 hover:border-emerald-300',
                                'labelColor' => 'text-emerald-900',
                                'valueColor' => 'text-[#059669]',
                                'subColor' => 'text-emerald-700/80',
                                'iconBg' => 'bg-emerald-100 text-[#059669] border border-emerald-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>'
                            ],
                            [
                                'key' => 'Total Weight',
                                'label' => 'Total Berat Panen',
                                'value' => number_format($totalWeight, 0, ',', '.') . ' kg',
                                'sub' => 'Total volume',
                                'cardBg' => 'bg-blue-50 border border-blue-200 hover:border-blue-300',
                                'labelColor' => 'text-blue-900',
                                'valueColor' => 'text-[#2563EB]',
                                'subColor' => 'text-blue-700/80',
                                'iconBg' => 'bg-blue-100 text-[#2563EB] border border-blue-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>'
                            ],
                            [
                                'key' => 'Shipments',
                                'label' => 'Total Pengiriman',
                                'value' => $totalShipments,
                                'sub' => 'Semua pengiriman',
                                'cardBg' => 'bg-teal-50 border border-teal-200 hover:border-teal-300',
                                'labelColor' => 'text-teal-900',
                                'valueColor' => 'text-[#0D9488]',
                                'subColor' => 'text-teal-700/80',
                                'iconBg' => 'bg-teal-100 text-[#0D9488] border border-teal-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>'
                            ],
                            [
                                'key' => 'Delivered',
                                'label' => 'Pengiriman Selesai',
                                'value' => $deliveredShipments,
                                'sub' => 'Telah tiba',
                                'cardBg' => 'bg-green-50 border border-green-200 hover:border-green-300',
                                'labelColor' => 'text-green-900',
                                'valueColor' => 'text-green-700',
                                'subColor' => 'text-green-700/80',
                                'iconBg' => 'bg-green-100 text-green-700 border border-green-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/></svg>'
                            ],
                            [
                                'key' => 'AI Analyses',
                                'label' => 'Analisis AI',
                                'value' => $totalAnalyses,
                                'sub' => 'Evaluasi risiko',
                                'cardBg' => 'bg-purple-50 border border-purple-200 hover:border-purple-300',
                                'labelColor' => 'text-purple-900',
                                'valueColor' => 'text-[#9333EA]',
                                'subColor' => 'text-purple-700/80',
                                'iconBg' => 'bg-purple-100 text-[#9333EA] border border-purple-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"/><path d="M9 13a4.5 4.5 0 0 0 3-4"/><path d="M6.003 5.125A3 3 0 0 0 6.401 6.5"/><path d="M3.477 10.896a4 4 0 0 1 .585-.396"/><path d="M6 18a4 4 0 0 1-1.967-.516"/><path d="M12 13h4"/><path d="M12 18h6a2 2 0 0 1 2 2v1"/><path d="M12 8h8"/><path d="M16 8V5a2 2 0 0 1 2-2"/><circle cx="16" cy="13" r=".5"/><circle cx="18" cy="3" r=".5"/><circle cx="20" cy="21" r=".5"/><circle cx="20" cy="8" r=".5"/></svg>'
                            ],
                            [
                                'key' => 'High Risk',
                                'label' => 'Risiko Tinggi',
                                'value' => $highRisk,
                                'sub' => 'Perlu perhatian',
                                'cardBg' => 'bg-red-50 border border-red-200 hover:border-red-300',
                                'labelColor' => 'text-red-900',
                                'valueColor' => 'text-[#DC2626]',
                                'subColor' => 'text-red-700/80',
                                'iconBg' => 'bg-red-100 text-[#DC2626] border border-red-200',
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>'
                            ],
                        ];
                    @endphp

                    @foreach ($items as $item)
                        @php
                            $content = "Detail informasi untuk " . $item['label'] . ".";
                            $wrapperStart = "<div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; color: #1e293b;'>";
                            $wrapperEnd = "</div>";
                            $emptyMsg = "<div style='color: #64748b; font-style: italic; text-align: center;'>Data belum tersedia saat ini.</div>";

                            $userId = \Illuminate\Support\Facades\Auth::id();

                            $ownedShipments = fn () => \App\Models\Shipment::query()
                                ->whereHas('harvest', function ($query) use ($userId) {
                                    $query->where('user_id', $userId);
                                });

                            $ownedHarvests = fn () => \App\Models\Harvest::query()
                                ->where('user_id', $userId);

                            $ownedAnalyses = fn () => \App\Models\AiAnalysis::query()
                                ->whereHas('shipment.harvest', function ($query) use ($userId) {
                                    $query->where('user_id', $userId);
                                });

                            switch ($item['key']) {
                                case 'Total Harvest':
                                    $harvests = $ownedHarvests()->get();

                                    if ($harvests->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $list = $harvests->pluck('commodity')
                                            ->filter()
                                            ->unique()
                                            ->map(fn ($commodity) => e($commodity))
                                            ->implode(', ');

                                        $content = $wrapperStart
                                            . "Terdapat total <strong>" . $totalHarvests
                                            . "</strong> data panen.<br><br>Komoditas: <strong>"
                                            . ($list ?: 'Belum tersedia')
                                            . "</strong>" . $wrapperEnd;
                                    }
                                    break;

                                case 'Total Weight':
                                    $weightDetails = $ownedHarvests()
                                        ->select('commodity')
                                        ->selectRaw('SUM(weight) as total_weight')
                                        ->groupBy('commodity')
                                        ->get();

                                    if ($weightDetails->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $inner = "<p style='margin-bottom:8px'>Rincian berat per komoditas:</p><ul style='list-style: disc; margin-left: 20px;'>";

                                        foreach ($weightDetails as $w) {
                                            $inner .= "<li><strong>" . e($w->commodity)
                                                . "</strong>: " . number_format($w->total_weight, 0, ',', '.')
                                                . " kg</li>";
                                        }

                                        $inner .= "</ul>";
                                        $content = $wrapperStart . $inner . $wrapperEnd;
                                    }
                                    break;

                                case 'Shipments':
                                    $recentShipments = $ownedShipments()
                                        ->with('harvest')
                                        ->latest()
                                        ->take(20)
                                        ->get();

                                    if ($recentShipments->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $inner = "<p style='margin-bottom:12px;font-weight:bold'>Pengiriman Terbaru</p><div class='custom-scrollbar' style='max-height:320px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#fff;'><ul style='list-style:none;padding:0;margin:0'>";

                                        foreach ($recentShipments as $s) {
                                            $statusColor = match ($s->status) {
                                                'Harvested' => '#f59e0b',
                                                'Packed' => '#3b82f6',
                                                'In Transit' => '#0d9488',
                                                'Delivered' => '#059669',
                                                default => '#64748b',
                                            };

                                            $statusIndo = match ($s->status) {
                                                'Harvested' => 'Baru Dipanen',
                                                'Packed' => 'Dikemas',
                                                'In Transit' => 'Dalam Perjalanan',
                                                'Delivered' => 'Telah Tiba',
                                                default => 'Tidak diketahui',
                                            };

                                            $commodityName = e($s->harvest->commodity ?? 'Tidak diketahui');
                                            $origin = e($s->origin ?? 'Tidak diketahui');
                                            $destination = e($s->destination ?? 'Tidak diketahui');

                                            $inner .= "<li style='padding:10px;margin-bottom:8px;border:1px solid #f1f5f9;border-radius:8px;'><strong>{$commodityName}</strong><br><span style='font-size:12px;color:#64748b'>{$origin} ➜ {$destination}</span><br><span style='display:inline-block;background:{$statusColor};color:white;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:bold;margin-top:4px;'>{$statusIndo}</span></li>";
                                        }

                                        $inner .= "</ul><div style='margin-top:10px;text-align:center;font-size:12px;color:#64748b;'>Menampilkan 20 pengiriman terbaru</div></div>";
                                        $content = $wrapperStart . $inner . $wrapperEnd;
                                    }
                                    break;

                                case 'Delivered':
                                    $deliveredList = $ownedShipments()
                                        ->where('status', 'Delivered')
                                        ->with('harvest')
                                        ->latest()
                                        ->take(5)
                                        ->get();

                                    if ($deliveredList->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $inner = "<p style='margin-bottom:8px'>Pengiriman terbaru yang telah tiba:</p><ul style='list-style: disc; margin-left: 20px;'>";

                                        foreach ($deliveredList as $s) {
                                            $inner .= "<li><strong>"
                                                . e($s->harvest->commodity ?? 'Tidak diketahui')
                                                . "</strong></li>";
                                        }

                                        $inner .= "</ul>";
                                        $content = $wrapperStart . $inner . $wrapperEnd;
                                    }
                                    break;

                                case 'AI Analyses':
                                    $recentAnalyses = $ownedAnalyses()
                                        ->with('shipment.harvest')
                                        ->latest()
                                        ->take(20)
                                        ->get();

                                    if ($recentAnalyses->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $inner = "<p style='margin-bottom:8px'>Analisis AI Terbaru</p><div class='custom-scrollbar' style='max-height:280px;overflow-y:auto;padding-right:8px;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#fff;'><ul style='list-style:none;padding:0'>";

                                        foreach ($recentAnalyses as $a) {
                                            $commodity = e($a->shipment->harvest->commodity ?? 'Tidak diketahui');
                                            $riskColor = $a->risk_level === 'High'
                                                ? '#ef4444'
                                                : ($a->risk_level === 'Medium' ? '#f59e0b' : '#059669');

                                            $riskDisplay = match ($a->risk_level) {
                                                'High' => 'Risiko Tinggi',
                                                'Medium' => 'Risiko Sedang',
                                                'Low' => 'Risiko Rendah',
                                                default => 'Tidak diketahui',
                                            };

                                            $inner .= "<li style='margin-bottom:8px;padding:8px;border-radius:8px;border:1px solid #f1f5f9;'><strong>Komoditas:</strong> {$commodity}<br><strong>Risiko:</strong> <span style='color:{$riskColor};font-weight:bold;'>{$riskDisplay}</span></li>";
                                        }

                                        $inner .= "</ul><div style='margin-top:12px;text-align:center;'><a href='" . route('ai-analysis.history') . "' style='display:inline-block;padding:6px 16px;background:#059669;color:white;border-radius:8px;font-weight:bold;font-size:12px;'>Lihat Semua Analisis →</a></div></div>";

                                        $content = $wrapperStart . $inner . $wrapperEnd;
                                    }
                                    break;

                                case 'High Risk':
                                    if ($priorityActionShipments->isEmpty()) {
                                        $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                    } else {
                                        $inner = "<p style='margin-bottom:8px'>Pengiriman aktif berisiko tinggi:</p><ul style='list-style: disc; margin-left: 20px;'>";

                                        foreach ($priorityActionShipments->take(5) as $h) {
                                            $urgencyIndo = match ($h['urgency_level'] ?? '') {
                                                'Immediate' => 'Segera',
                                                'High' => 'Tinggi',
                                                'Elevated' => 'Meningkat',
                                                default => 'Rutin',
                                            };

                                            $inner .= "<li><strong>" . e($h['commodity'])
                                                . "</strong> — Risiko " . e($h['risk'])
                                                . "/100 · Urgensi " . e($urgencyIndo)
                                                . "</li>";
                                        }

                                        $inner .= "</ul>";
                                        $content = $wrapperStart . $inner . $wrapperEnd;
                                    }
                                    break;
                            }
                        @endphp

                        <button type="button"
                                onclick="openModal(this)"
                                data-title="{{ $item['label'] }}"
                                data-content='{!! htmlspecialchars($content) !!}'
                                aria-label="Lihat detail untuk {{ $item['label'] }}"
                                class="agri-card {{ $item['cardBg'] }} p-4 sm:p-5 text-left cursor-pointer agri-card-hover flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-[#059669] focus:ring-offset-2">
                            <div class="flex items-start justify-between gap-2 w-full mb-3">
                                <span class="text-xs font-extrabold {{ $item['labelColor'] }} leading-snug">{{ $item['label'] }}</span>
                                <span class="icon-badge w-9 h-9 rounded-xl {{ $item['iconBg'] }} flex items-center justify-center shrink-0 shadow-2xs" aria-hidden="true">
                                    {!! $item['svg'] !!}
                                </span>
                            </div>
                            <div>
                                <p class="text-2xl font-black {{ $item['valueColor'] }} tracking-tight truncate">{{ $item['value'] }}</p>
                                <p class="text-[11px] font-semibold {{ $item['subColor'] }} mt-1">{{ $item['sub'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>


            {{-- ================================================================= --}}
            {{-- 3. RINGKASAN ANALISIS AI                                          --}}
            {{-- ================================================================= --}}
            <section aria-label="Ringkasan Analisis AI" class="mb-8 animate-card delay-2">
                <div class="agri-card p-6 sm:p-7 bg-white border border-purple-200/90 relative overflow-hidden">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch relative z-10">

                        {{-- Kolom Kiri: Ringkasan Situasi AI + Wawasan Operasional AI --}}
                        <div class="lg:col-span-7 flex flex-col justify-between gap-4">

                            <div class="p-5 rounded-2xl bg-purple-50/75 border border-purple-200 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 border border-purple-300 text-purple-900 text-xs font-extrabold tracking-wide">
                                        <svg class="w-3.5 h-3.5 text-[#9333EA]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"/><path d="M12 13h4"/><path d="M12 8h8"/>
                                        </svg>
                                        <span>Ringkasan Analisis AI</span>
                                    </span>
                                    <span class="text-xs font-bold text-purple-700">Berdasarkan data pengiriman tercatat</span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black text-[#0F172A] tracking-tight">
                                    Kondisi Pengiriman & Panduan AI Hari Ini
                                </h2>

                                <p class="text-[#0F172A]/85 text-sm sm:text-base leading-relaxed">
                                    @if($highRisk > 0)
                                        Dari <strong class="text-[#065F46]">{{ $activeShipmentCount }} pengiriman aktif</strong>, terdapat <strong class="text-[#DC2626]">{{ $highRisk }} pengiriman berisiko tinggi</strong>
                                        @if($immediateAttentionCount > 0)
                                            dan <strong class="text-[#D97706]">{{ $immediateAttentionCount }} memerlukan tindakan segera</strong> untuk mencegah penurunan mutu atau keterlambatan.
                                        @else
                                            yang perlu ditinjau sesuai batas waktu pengiriman saat ini.
                                        @endif
                                    @elseif($mediumRisk > 0)
                                        Seluruh <strong class="text-[#065F46]">{{ $activeShipmentCount }} pengiriman aktif</strong> bebas dari risiko tinggi. Tetap pantau <strong class="text-[#D97706]">{{ $mediumRisk }} pengiriman berisiko sedang</strong>.
                                    @else
                                        Seluruh <strong class="text-[#065F46]">{{ $activeShipmentCount }} pengiriman aktif</strong> berjalan aman pada tingkat risiko rendah.
                                    @endif
                                </p>
                            </div>

                            {{-- Wawasan Operasional AI --}}
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-[#059669] border border-emerald-300 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#059669]">Wawasan Operasional AI</p>
                                    <p class="text-xs sm:text-sm text-emerald-950 font-semibold mt-0.5 leading-relaxed">"{{ $aiInsightText }}"</p>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Metrik Risiko & Status Mesin AI --}}
                        <div class="lg:col-span-5 grid grid-cols-2 gap-3.5">

                            {{-- Tindakan Segera --}}
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-amber-900">Tindakan Segera</span>
                                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-[#D97706] border border-amber-200 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <p class="text-3xl font-black text-[#D97706]">{{ $immediateAttentionCount }}</p>
                                    <p class="text-[11px] font-semibold text-amber-800/85 mt-0.5">Pengiriman mendesak</p>
                                </div>
                            </div>

                            {{-- Risiko Rendah & Sedang --}}
                            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-green-900">Rendah & Sedang</span>
                                    <span class="w-8 h-8 rounded-lg bg-green-100 text-[#059669] border border-green-200 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <p class="text-3xl font-black text-[#059669]">{{ $lowRisk + $mediumRisk }}</p>
                                    <p class="text-[11px] font-semibold text-green-800/85 mt-0.5">{{ $lowRisk }} Rendah · {{ $mediumRisk }} Sedang</p>
                                </div>
                            </div>

                            {{-- Status Mesin AI --}}
                            <div class="col-span-2 p-4 rounded-2xl bg-[#065F46] text-white border border-[#047857] flex flex-wrap items-center justify-between gap-3 shadow-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-white/15 text-[#A7F3D0] flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-extrabold text-white">Mesin Keputusan & Rute AI</p>
                                        <p class="text-[11px] text-[#D1FAE5]">Penilaian risiko & optimasi rute aktif</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold px-3 py-1 rounded-full bg-[#059669] text-white border border-[#A7F3D0]/40">
                                    <span class="w-2 h-2 rounded-full bg-[#A7F3D0] animate-pulse"></span>
                                    Sistem Aktif
                                </span>
                            </div>

                        </div>

                    </div>
                </div>
            </section>


            {{-- ================================================================= --}}
            {{-- 4. PEMANTAUAN LINGKUNGAN (Cuaca & Prakiraan Rute)                 --}}
            {{-- ================================================================= --}}
            <section aria-label="Pemantauan Lingkungan" class="mb-8 animate-card delay-3">
                <div class="agri-card p-6 sm:p-8 bg-white border border-cyan-200/80">

                    {{-- Header Seksi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200/80">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-cyan-100 text-[#0891B2] border border-cyan-200 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M21.54 15H17a2 2 0 0 0-2 2v4.54"/><path d="M7 3.34V5a3 3 0 0 0 3 3a2 2 0 0 1 2 2c0 1.1.9 2 2 2a2 2 0 0 0 2-2c0-1.1.9-2 2-2h3.17"/><path d="M11 21.95V18a2 2 0 0 0-2-2a2 2 0 0 1-2-2v-1a2 2 0 0 0-2-2H2.05"/><circle cx="12" cy="12" r="10"/>
                                </svg>
                            </div>
                            <div>
                                <div class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-[#0891B2]">
                                    <span>Pemantauan Lingkungan</span>
                                </div>
                                <h2 class="text-2xl font-black text-[#0F172A] tracking-tight">Cuaca & Kondisi Rute</h2>
                                <p class="text-[#64748B] text-sm mt-0.5">
                                    Data cuaca Open-Meteo dan prakiraan 6 jam ke depan untuk menjaga kesegaran hasil panen di perjalanan.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-start sm:self-center">
                            <div class="px-3.5 py-2 rounded-xl bg-teal-50 border border-teal-200 flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#0D9488] animate-pulse"></span>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Terakhir Diperbarui</p>
                                    <p class="text-xs font-black text-teal-950" id="environmentTime">{{ now()->format('H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Pemantauan Cuaca --}}
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">

                        {{-- Kiri: Pantauan Satelit --}}
                        <div class="xl:col-span-4 bg-gradient-to-b from-[#065F46] to-[#0F172A] text-white rounded-2xl p-5 flex flex-col justify-between relative overflow-hidden shadow-md">
                            <div class="flex justify-between items-center z-10">
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-widest text-[#A7F3D0]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/><path d="M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5"/><circle cx="12" cy="12" r="2"/><path d="M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5"/><path d="M19.1 4.9C23 8.8 23 15.1 19.1 19"/>
                                    </svg>
                                    Pantauan Satelit
                                </span>
                                <span class="text-[10px] font-bold px-2.5 py-0.5 bg-[#059669]/40 text-[#A7F3D0] rounded-full border border-[#A7F3D0]/30">Terhubung</span>
                            </div>

                            <div class="my-2 relative flex items-center justify-center h-[215px] w-full overflow-hidden">
                                <div class="absolute w-48 h-48 bg-[#0D9488]/30 rounded-full blur-3xl"></div>
                                <div class="relative z-10 w-full h-full flex items-center justify-center [mask-image:radial-gradient(circle,black_55%,transparent_80%)]">
                                    <div id="weatherOrb" class="w-full h-full flex items-center justify-center bg-transparent"
                                         data-rain="{{ $environment['weather']['rain'] ?? 0 }}"
                                         data-cloud="{{ $environment['weather']['cloud_cover'] ?? 0 }}"
                                         data-wind="{{ $environment['weather']['wind_speed_10m'] ?? 0 }}"
                                         data-temp="{{ $environment['weather']['temperature_2m'] ?? 25 }}">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5 text-xs z-10">
                                <div class="p-3 bg-white/10 backdrop-blur-xs rounded-xl border border-white/15">
                                    <p class="text-[#A7F3D0] text-[10px] font-bold uppercase tracking-wider">Wilayah Pantauan</p>
                                    <p class="font-extrabold text-white text-xs mt-0.5 truncate">{{ data_get($environment, 'location', 'Tidak diketahui') }}</p>
                                </div>
                                <div class="p-3 bg-white/10 backdrop-blur-xs rounded-xl border border-white/15">
                                    <p class="text-[#A7F3D0] text-[10px] font-bold uppercase tracking-wider">Sumber Cuaca</p>
                                    <p class="font-extrabold text-[#CCFBF1] text-xs mt-0.5">Open-Meteo API</p>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: 6 Kartu Metrik Cuaca --}}
                        <div class="xl:col-span-8 flex flex-col justify-between gap-5">

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                                {{-- 1. Suhu --}}
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-amber-900">Suhu</span>
                                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-[#D97706] border border-amber-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-[#D97706] mt-2">{{ round($environment['weather']['temperature_2m']) }}°C</p>
                                </div>

                                {{-- 2. Kelembapan --}}
                                <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-teal-900">Kelembapan</span>
                                        <span class="w-8 h-8 rounded-xl bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-[#0D9488] mt-2">{{ $environment['weather']['relative_humidity_2m'] }}%</p>
                                </div>

                                {{-- 3. Curah Hujan --}}
                                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-blue-900">Curah Hujan</span>
                                        <span class="w-8 h-8 rounded-xl bg-blue-100 text-[#2563EB] border border-blue-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-[#2563EB] mt-2">{{ $environment['weather']['rain'] }} <span class="text-xs font-bold text-blue-700/80">mm</span></p>
                                </div>

                                {{-- 4. Kecepatan Angin --}}
                                <div class="p-4 rounded-2xl bg-cyan-50 border border-cyan-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-cyan-900">Kecepatan Angin</span>
                                        <span class="w-8 h-8 rounded-xl bg-cyan-100 text-[#0891B2] border border-cyan-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-[#0891B2] mt-2">{{ round($environment['weather']['wind_speed_10m']) }} <span class="text-xs font-bold text-cyan-700/80">km/jam</span></p>
                                </div>

                                {{-- 5. Tutupan Awan --}}
                                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-sky-900">Tutupan Awan</span>
                                        <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 border border-sky-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-sky-700 mt-2">{{ $environment['weather']['cloud_cover'] }}%</p>
                                </div>

                                {{-- 6. Kesesuaian Cuaca --}}
                                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-extrabold text-emerald-900">Kesesuaian Cuaca</span>
                                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 2v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="M20 12h2"/><path d="m19.07 4.93-1.41 1.41"/><path d="M15.947 12.65a4 4 0 0 0-5.925-4.128"/><path d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <p class="text-2xl font-black text-[#059669] mt-2">{{ $environment['weather_suitability_score'] ?? '—' }}<span class="text-xs font-bold text-emerald-700">%</span></p>
                                </div>
                            </div>

                            {{-- Panduan Cuaca Rute & Indikator --}}
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                                <div class="md:col-span-7 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#059669] mb-1">Panduan Cuaca Rute</p>
                                        <p class="text-sm text-emerald-950 leading-relaxed">{{ $environment['recommendation'] }}</p>
                                    </div>
                                </div>

                                <div class="md:col-span-5 space-y-3 bg-cyan-50/75 p-4 rounded-2xl border border-cyan-200 flex flex-col justify-center">
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1">
                                            <span class="text-cyan-900" title="Kelengkapan data cuaca yang tersedia">Kelengkapan Data Cuaca</span>
                                            <span class="text-[#059669]">{{ $environment['data_coverage'] ?? 0 }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white overflow-hidden border border-cyan-200/70">
                                            <div class="h-full bg-[#059669] rounded-full transition-all duration-500" style="width:{{ $environment['data_coverage'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1">
                                            <span class="text-cyan-900" title="Indeks dampak kondisi cuaca terhadap operasional">Tingkat Dampak Cuaca</span>
                                            <span class="text-[#D97706]">{{ $environment['environmental_condition_index'] ?? '—' }}/100</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white overflow-hidden border border-cyan-200/70">
                                            <div class="h-full bg-[#D97706] rounded-full transition-all duration-500" style="width: {{ $environment['environmental_condition_index'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Grafik Prakiraan Cuaca 6 Jam --}}
                    <div class="mt-8 pt-6 border-t border-slate-200/80">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#059669]">Prakiraan 6 Jam</p>
                                <h3 class="text-lg font-black text-[#0F172A]">Tren Cuaca Rute Mendatang</h3>
                            </div>
                            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Metrik Prakiraan Cuaca">
                                <button type="button" class="weather-tab active" data-type="temp">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                                    <span>Suhu</span>
                                </button>
                                <button type="button" class="weather-tab" data-type="humidity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                                    <span>Kelembapan</span>
                                </button>
                                <button type="button" class="weather-tab" data-type="wind">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/></svg>
                                    <span>Angin</span>
                                </button>
                                <button type="button" class="weather-tab" data-type="rain">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/></svg>
                                    <span>Curah Hujan</span>
                                </button>
                                <button type="button" class="weather-tab" data-type="cloud">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                                    <span>Tutupan Awan</span>
                                </button>
                            </div>
                        </div>

                        <div class="h-[280px] sm:h-[300px] w-full p-4 rounded-2xl bg-sky-50/45 border border-sky-200/80">
                            <canvas id="temperatureChart"></canvas>
                        </div>
                    </div>

                </div>
            </section>


            {{-- ================================================================= --}}
            {{-- 5. ANALITIK OPERASIONAL (3 Kartu Kinerja Utama)                   --}}
            {{-- ================================================================= --}}
            <section aria-label="Analitik Operasional" class="mb-8 animate-card">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#059669]">Analitik Operasional</p>
                        <h2 class="text-2xl font-black text-[#0F172A] tracking-tight">Kinerja Logistik Utama</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- 1. Rata-rata Risiko Operasional --}}
                    <div class="agri-card p-6 agri-card-hover bg-amber-50 border border-amber-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-amber-900">Rata-rata Risiko Operasional</span>
                                    <div class="mt-2 flex items-baseline gap-1.5">
                                        <p class="text-4xl font-black text-[#D97706]">{{ $averageOperationalRisk }}</p>
                                        <span class="text-sm font-bold text-amber-800/80">/100</span>
                                    </div>
                                </div>
                                <div class="icon-badge w-11 h-11 rounded-2xl bg-amber-100 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-amber-900/80 mt-3 leading-relaxed">
                                Rata-rata tingkat risiko pengiriman aktif (skor lebih rendah berarti perjalanan lebih aman).
                            </p>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-amber-200/80 flex items-center justify-between text-xs font-bold">
                            <span class="text-amber-900/80">Porsi risiko tinggi aktif</span>
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded-full">{{ $highRiskShare }}%</span>
                        </div>
                    </div>

                    {{-- 2. Estimasi Emisi Transportasi --}}
                    <div class="agri-card p-6 agri-card-hover bg-emerald-50 border border-emerald-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-900">Estimasi Emisi Transportasi</span>
                                    <div class="mt-2 flex items-baseline gap-1.5">
                                        <p class="text-4xl font-black text-[#059669]">{{ number_format($currentCarbon, 1, ',', '.') }}</p>
                                        <span class="text-sm font-bold text-emerald-800">kg CO₂e</span>
                                    </div>
                                </div>
                                <div class="icon-badge w-11 h-11 rounded-2xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-900/80 mt-3 leading-relaxed">
                                Perkiraan emisi CO₂e angkutan darat berdasarkan berat muatan dan jarak tempuh.
                            </p>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-emerald-200/80 flex items-center justify-between text-xs">
                            <span class="font-semibold text-emerald-900/80">Estimasi perhitungan, bukan pengukuran langsung</span>
                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-900 font-bold rounded-full border border-emerald-300">CO₂e</span>
                        </div>
                    </div>

                    {{-- 3. Tingkat Penyelesaian Pengiriman --}}
                    <div class="agri-card p-6 agri-card-hover bg-teal-50 border border-teal-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-teal-900">Pengiriman Selesai</span>
                                    <div class="mt-2 flex items-baseline gap-1.5">
                                        <p class="text-4xl font-black text-[#0D9488]">{{ $currentEfficiency }}%</p>
                                    </div>
                                </div>
                                <div class="icon-badge w-11 h-11 rounded-2xl bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-teal-900/80 mt-3 leading-relaxed">
                                Persentase dari seluruh pengiriman tercatat yang telah tiba di lokasi tujuan.
                            </p>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-teal-200/80 flex items-center justify-between text-xs font-bold">
                            <span class="text-teal-900/80">Jumlah telah tiba</span>
                            <span class="px-2.5 py-1 bg-teal-100 text-teal-900 border border-teal-300 rounded-full">{{ $deliveredShipments }} / {{ $totalShipments }} pengiriman</span>
                        </div>
                    </div>

                </div>
            </section>


            {{-- ================================================================= --}}
            {{-- 6. OPERASIONAL UTAMA (Tindakan Prioritas & Visualisasi Grafik)    --}}
            {{-- ================================================================= --}}
            <section aria-label="Operasional Utama" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                {{-- Kolom Kiri (7 col): Tindakan Prioritas --}}
                <div class="lg:col-span-7">
                    <div class="agri-card p-6 sm:p-7 bg-white border border-red-200/90">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-red-100">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 text-[#DC2626] border border-red-200 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg font-black text-[#0F172A] tracking-tight">Tindakan Prioritas</h2>
                                        <span class="text-xs font-extrabold bg-red-100 text-[#DC2626] border border-red-200 px-2.5 py-0.5 rounded-full">
                                            {{ $highRisk }} Risiko Tinggi
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#64748B] mt-0.5">Pengiriman yang memerlukan perhatian beserta langkah yang disarankan.</p>
                                </div>
                            </div>

                            <a href="{{ route('ai-analysis.index') }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-[#059669] text-[#065F46] hover:text-white border border-emerald-200 text-xs font-bold transition-all self-start sm:self-center focus:outline-none focus:ring-2 focus:ring-[#059669]">
                                <span>Lihat Semua Analisis</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        <div class="max-h-[540px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3.5">
                            @forelse($priorityActionShipments as $alert)
                                @php
                                    $riskScore = (int) ($alert['risk'] ?? 0);
                                    if ($riskScore >= 60) {
                                        $riskBadgeClass = 'bg-red-100 text-[#DC2626] border-red-200';
                                        $cardBgClass = 'bg-red-50/75 border-red-200 hover:bg-red-50 hover:border-red-300';
                                        $iconBoxClass = 'bg-red-100 text-[#DC2626] border-red-200';
                                        $riskLabel = 'Risiko Tinggi';
                                    } elseif ($riskScore >= 30) {
                                        $riskBadgeClass = 'bg-amber-100 text-[#D97706] border-amber-200';
                                        $cardBgClass = 'bg-amber-50/75 border-amber-200 hover:bg-amber-50 hover:border-amber-300';
                                        $iconBoxClass = 'bg-amber-100 text-[#D97706] border-amber-200';
                                        $riskLabel = 'Risiko Sedang';
                                    } else {
                                        $riskBadgeClass = 'bg-emerald-100 text-[#059669] border-emerald-200';
                                        $cardBgClass = 'bg-emerald-50/75 border-emerald-200 hover:bg-emerald-50 hover:border-emerald-300';
                                        $iconBoxClass = 'bg-emerald-100 text-[#059669] border-emerald-200';
                                        $riskLabel = 'Risiko Rendah';
                                    }

                                    $urgencyRaw = $alert['urgency_level'] ?? '';
                                    $urgencyBadgeClass = match($urgencyRaw) {
                                        'Immediate' => 'bg-[#DC2626] text-white',
                                        'High' => 'bg-[#D97706] text-white',
                                        default => 'bg-amber-200 text-amber-900',
                                    };
                                    $urgencyLabel = match($urgencyRaw) {
                                        'Immediate' => 'Urgensi Segera',
                                        'High' => 'Urgensi Tinggi',
                                        'Elevated' => 'Urgensi Meningkat',
                                        default => 'Urgensi Rutin',
                                    };
                                @endphp

                                <a href="{{ route('shipments.show', $alert['shipment_id']) }}"
                                   class="block p-4 rounded-2xl border {{ $cardBgClass }} hover:shadow-md transition-all group focus:outline-none focus:ring-2 focus:ring-[#059669]">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                            <div class="w-10 h-10 rounded-xl {{ $iconBoxClass }} border flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="font-black text-base text-[#0F172A] group-hover:text-[#059669] transition-colors">
                                                        {{ $alert['commodity'] }}
                                                    </h3>
                                                    <span class="px-2.5 py-0.5 rounded-full border {{ $riskBadgeClass }} text-[11px] font-extrabold">
                                                        {{ $riskLabel }} · {{ $alert['risk'] }}/100
                                                    </span>
                                                    <span class="px-2.5 py-0.5 rounded-full {{ $urgencyBadgeClass }} text-[10px] font-extrabold uppercase tracking-wider">
                                                        {{ $urgencyLabel }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-1.5 text-xs font-semibold text-[#475569] mt-1.5">
                                                    <svg class="w-3.5 h-3.5 text-[#0D9488] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                                                    </svg>
                                                    <span>{{ $alert['origin'] }}</span>
                                                    <span class="text-[#059669] font-black" aria-hidden="true">→</span>
                                                    <span>{{ $alert['destination'] }}</span>
                                                </div>

                                                <p class="text-xs text-[#0F172A]/85 mt-2.5 leading-relaxed bg-white/90 px-3 py-2 rounded-xl border border-slate-200/80">
                                                    <span class="font-extrabold text-[#047857]">Rekomendasi Tindakan:</span> {{ \Illuminate\Support\Str::limit($alert['recommended_action'], 115) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="shrink-0 self-end sm:self-center">
                                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 group-hover:border-[#059669] group-hover:bg-[#059669] text-[#059669] group-hover:text-white font-bold text-xs transition-all shadow-2xs">
                                                <span>Lihat Detail</span>
                                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </span>
                                        </div>

                                    </div>
                                </a>
                            @empty
                                <div class="py-12 px-4 text-center rounded-2xl bg-emerald-50 border border-emerald-200">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#059669] border border-emerald-200 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <p class="font-black text-base text-[#065F46]">Tidak ada pengiriman yang memerlukan tindakan mendesak</p>
                                    <p class="text-xs text-[#047857] mt-1">Seluruh pengiriman aktif berjalan aman dalam batas risiko normal.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan (5 col): Grafik Sebaran Risiko & Status Pengiriman --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Grafik Sebaran Risiko Aktif --}}
                    <div class="agri-card p-6 bg-amber-50/45 border border-amber-200">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-amber-200/70">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-black text-sm text-[#0F172A]">Sebaran Risiko Aktif</h3>
                                    <p class="text-xs text-amber-900/75">Pengiriman aktif berdasarkan tingkat risiko</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 bg-white text-[#065F46] rounded-full border border-emerald-200">
                                {{ $activeShipmentCount }} Aktif
                            </span>
                        </div>
                        <div class="h-56 p-3 rounded-xl bg-white/90 border border-amber-100">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>

                    {{-- Grafik Status Pengiriman --}}
                    <div class="agri-card p-6 bg-teal-50/45 border border-teal-200">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-teal-200/70">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-100 text-[#0D9488] border border-teal-200 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-black text-sm text-[#0F172A]">Status Pengiriman</h3>
                                    <p class="text-xs text-teal-900/75">Tahapan seluruh pengiriman tercatat</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 bg-white text-[#0D9488] rounded-full border border-teal-200">
                                {{ $totalShipments }} Total
                            </span>
                        </div>
                        <div class="h-56 p-3 rounded-xl bg-white/90 border border-teal-100">
                            <canvas id="shipmentStatusChart"></canvas>
                        </div>
                    </div>

                </div>

            </section>

        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- MODAL DETAIL KARTU KPI                                            --}}
    {{-- ================================================================= --}}
    <div id="myModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 id="modalTitle" class="text-xl font-black text-[#0F172A]"></h3>
                <button type="button" onclick="closeModal()" aria-label="Tutup jendela" class="text-[#64748B] hover:text-[#0F172A] p-1.5 rounded-lg hover:bg-slate-100 transition-colors">✕</button>
            </div>
            <div id="modalContent" class="text-[#64748B] text-sm leading-relaxed"></div>
            <button type="button" onclick="closeModal()" class="mt-6 w-full bg-[#059669] text-white py-3 rounded-xl font-bold hover:bg-[#047857] transition-colors focus:outline-none focus:ring-2 focus:ring-[#059669] focus:ring-offset-2">
                Tutup
            </button>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- SCRIPTS & INTEGRASI CHART.JS                                      --}}
    {{-- ================================================================= --}}
    <script>
        function openModal(element) {
            const title = element.getAttribute('data-title');
            const content = element.getAttribute('data-content');
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('myModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('myModal');
            if (event.target == modal) { closeModal(); }
        }
    </script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        // 1. Grafik Donat Sebaran Risiko
        const ctx = document.getElementById('riskChart').getContext('2d');
        Chart.register(ChartDataLabels);
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi'],
                datasets: [{
                    data: [{{ $lowRisk }}, {{ $mediumRisk }}, {{ $highRisk }}],
                    backgroundColor: ['#059669', '#D97706', '#DC2626'],
                    hoverBackgroundColor: ['#047857', '#B45309', '#B91C1C'],
                    borderWidth: 3,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#0F172A',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: { weight: 'bold', size: 12 }
                        }
                    },
                    datalabels: {
                        color: '#FFFFFF',
                        font: { weight: 'bold', size: 12 },
                        formatter: (value, ctx) => {
                            if (value === 0) return "";
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            return ((value * 100) / sum).toFixed(0) + "%";
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                let sum = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = sum > 0 ? ((value / sum) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${value} pengiriman (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // 2. Grafik Batang Status Pengiriman
        const shipmentCtx = document.getElementById('shipmentStatusChart');
        new Chart(shipmentCtx, {
            type: 'bar',
            data: {
                labels: ['Baru Dipanen', 'Dikemas', 'Dalam Perjalanan', 'Telah Tiba'],
                datasets: [{
                    data: [{{ $statusHarvested }}, {{ $statusPacked }}, {{ $statusTransit }}, {{ $statusDelivered }}],
                    borderRadius: 10,
                    backgroundColor: ['#D97706', '#2563EB', '#0D9488', '#059669']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#FFFFFF',
                        anchor: 'center',
                        align: 'center',
                        font: { size: 12, weight: 'bold' },
                        formatter: function(value){ return value > 0 ? value : ''; }
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} pengiriman`;
                            }
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: '#0F172A', font: { weight: 'bold', size: 11 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: '#64748B', precision: 0 }, grid: { color: 'rgba(226, 232, 240, 0.7)' } }
                }
            },
            plugins: [ChartDataLabels]
        });

        // 3. Grafik Tren Prakiraan Cuaca 6 Jam
        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById("temperatureChart");
            if (!ctx) return;

            const weatherSeries = {
                temp: @json(collect($weatherTrend)->pluck('temp')),
                humidity: @json(collect($weatherTrend)->pluck('humidity')),
                wind: @json(collect($weatherTrend)->pluck('wind')),
                rain: @json(collect($weatherTrend)->pluck('rain')),
                cloud: @json(collect($weatherTrend)->pluck('cloud'))
            };

            const weatherColor = {
                temp: "#059669",
                humidity: "#0D9488",
                wind: "#0891B2",
                rain: "#2563EB",
                cloud: "#64748B"
            };

            const weatherChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: @json(collect($weatherTrend)->pluck('time')),
                    datasets: [{
                        label: "Suhu",
                        data: weatherSeries.temp,
                        borderColor: "#059669",
                        borderWidth: 3,
                        fill: true,
                        tension: .4,
                        pointRadius: 4,
                        pointBackgroundColor: "#FFFFFF",
                        pointBorderWidth: 2.5,
                        pointHoverRadius: 6,
                        backgroundColor: (ctx) => {
                            const chart = ctx.chart;
                            const { ctx: canvas, chartArea } = chart;
                            if (!chartArea) return null;
                            const gradient = canvas.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, "rgba(5, 150, 105, 0.25)");
                            gradient.addColorStop(1, "rgba(5, 150, 105, 0.0)");
                            return gradient;
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label;
                                    const value = context.parsed.y;
                                    if (label === "Peluang Hujan" || label === "Kelembapan" || label === "Tutupan Awan") return label + ": " + value + "%";
                                    if (label === "Suhu") return label + ": " + value + "°C";
                                    if (label === "Kecepatan Angin") return label + ": " + value + " km/jam";
                                    return label + ": " + value;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: "#0F172A", font: { weight: 'bold' } } },
                        y: { grid: { color: "rgba(226, 232, 240, 0.75)" }, ticks: { color: "#64748B" } }
                    }
                }
            });

            document.querySelectorAll(".weather-tab").forEach(tab => {
                tab.addEventListener("click", () => {
                    document.querySelectorAll(".weather-tab").forEach(btn => btn.classList.remove("active"));
                    tab.classList.add("active");
                    const type = tab.dataset.type;
                    const labels = {
                        temp: "Suhu",
                        humidity: "Kelembapan",
                        wind: "Kecepatan Angin",
                        rain: "Peluang Hujan",
                        cloud: "Tutupan Awan"
                    };
                    weatherChart.data.datasets[0].label = labels[type];
                    weatherChart.data.datasets[0].data = weatherSeries[type];
                    weatherChart.data.datasets[0].borderColor = weatherColor[type];
                    weatherChart.update('active');
                });
            });
        });
    </script>
</x-app-layout>