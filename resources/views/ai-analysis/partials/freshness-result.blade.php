@php
    $qualityPrediction = session('quality_prediction', []);
    $predictionAvailable = $qualityPrediction['prediction_available'] ?? false;
    $isStorageStability = !empty($qualityPrediction['storage_stability_reference_available']);

    $qualityAtArrival = session('quality_at_arrival');
    $rawQualityStatus = session('quality_status', 'Belum tersedia');
    $qualityStatus = match ($rawQualityStatus) {
        'Excellent' => 'Sangat Baik',
        'Good' => 'Baik',
        'At Risk' => 'Berisiko',
        'Poor' => 'Kurang Baik',
        'Very Poor' => 'Sangat Kurang',
        'Unavailable', null, '' => 'Belum tersedia',
        default => $rawQualityStatus,
    };

    $qualityStatusBadge = match ($rawQualityStatus) {
        'Excellent', 'Good' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
        'Fair', 'Moderate', 'Warning' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
        'At Risk', 'Poor', 'Very Poor' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
        default => 'bg-emerald-900/60 text-emerald-200 border-emerald-700/60',
    };

    $remainingShelfLife = session('predicted_remaining_shelf_life_days');
    $shipmentData = session('shipment_data', []);

    $riskLevel = session('risk_level');
    $riskLower = strtolower((string) $riskLevel);
    $riskDisplayLevel = match (true) {
        str_contains($riskLower, 'crit') => 'Kritis',
        str_contains($riskLower, 'high') => 'Tinggi',
        str_contains($riskLower, 'med') || str_contains($riskLower, 'mod') => 'Sedang',
        str_contains($riskLower, 'low') => 'Rendah',
        default => 'Tidak diketahui',
    };
    $riskIndex = (int) (session('risk_index_value') ?? 0);

    // Perhitungan cincin progres kesegaran (keliling untuk r=34 adalah ~213.6)
    $freshnessVal = $qualityAtArrival !== null ? max(0, min(100, (float) $qualityAtArrival)) : 0;
    $circumference = 213.6;
    $dashOffset = $circumference - ($circumference * $freshnessVal / 100);

    $freshnessColor = match (true) {
        !$predictionAvailable || $qualityAtArrival === null => 'text-emerald-200/70',
        $qualityAtArrival >= 85 => 'text-emerald-300',
        $qualityAtArrival >= 70 => 'text-emerald-300',
        $qualityAtArrival >= 50 => 'text-amber-300',
        default => 'text-rose-300',
    };

    $freshnessExplanation = match (true) {
        $isStorageStability => 'Komoditas kering: skor kesegaran produk segar tidak berlaku. Pantau kadar air dan kelembapan penyimpanan.',
        !$predictionAvailable || $qualityAtArrival === null => 'Skor kesegaran belum dapat dihitung dengan data panen saat ini.',
        $qualityAtArrival >= 85 => 'Produk diperkirakan tiba dalam kondisi sangat baik dan siap dipasarkan.',
        $qualityAtArrival >= 70 => 'Produk diperkirakan tiba dalam kondisi baik dengan penurunan alami yang wajar.',
        $qualityAtArrival >= 50 => 'Penurunan kualitas produk diperkirakan terjadi selama perjalanan. Tangani dengan hati-hati.',
        default => 'Penurunan kualitas yang cukup besar berisiko terjadi saat tiba tanpa penanganan segera.',
    };

    $riskTone = match (true) {
        str_contains($riskLower, 'crit') => [
            'badge' => 'bg-red-900/80 text-red-100 border-red-500/60',
            'bar' => 'bg-gradient-to-r from-red-600 to-red-800',
            'desc' => 'Indeks risiko pengiriman berada pada tingkat kritis. Penanganan segera sangat diperlukan untuk mencegah kerusakan.',
        ],
        str_contains($riskLower, 'high') => [
            'badge' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
            'bar' => 'bg-gradient-to-r from-orange-500 to-rose-600',
            'desc' => 'Indeks risiko pengiriman berada pada tingkat tinggi. Perhatian segera diperlukan untuk mencegah penurunan mutu.',
        ],
        str_contains($riskLower, 'med') || str_contains($riskLower, 'mod') => [
            'badge' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
            'bar' => 'bg-gradient-to-r from-amber-300 to-orange-500',
            'desc' => 'Indeks risiko pengiriman berada pada tingkat sedang. Tinjau faktor utama dan ikuti langkah di bawah ini.',
        ],
        default => [
            'badge' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
            'bar' => 'bg-gradient-to-r from-emerald-300 to-emerald-500',
            'desc' => 'Indeks risiko pengiriman berada pada tingkat rendah. Kondisi pengiriman saat ini masih dalam batas aman.',
        ],
    };

    $displayShelfLife = $remainingShelfLife ?? ($shipmentData['remaining_days'] ?? null);
    $shelfLifeTone = match (true) {
        $displayShelfLife === null => [
            'pct' => 0,
            'bar' => 'bg-teal-700/70',
            'desc' => 'Data sisa masa simpan belum tersedia untuk pengiriman ini.',
        ],
        $displayShelfLife <= 1 => [
            'pct' => 15,
            'bar' => 'bg-gradient-to-r from-rose-400 to-rose-500',
            'desc' => 'Sisa masa simpan sangat singkat saat tiba. Utamakan penjualan atau penanganan segera.',
        ],
        $displayShelfLife <= 3 => [
            'pct' => 45,
            'bar' => 'bg-gradient-to-r from-teal-300 to-amber-300',
            'desc' => 'Sisa masa simpan terbatas saat tiba. Hindari segala bentuk penundaan pengiriman.',
        ],
        default => [
            'pct' => 85,
            'bar' => 'bg-gradient-to-r from-teal-300 to-cyan-400',
            'desc' => 'Sisa masa simpan produk masih mencukupi untuk perjalanan yang direncanakan.',
        ],
    };
@endphp

<section aria-label="Kondisi Pengiriman" class="mb-8">
    <div class="mb-4">
        <h3 class="text-lg sm:text-xl font-black text-slate-900">Kondisi Pengiriman</h3>
        <p class="text-sm text-slate-600 mt-0.5">Ringkasan perkiraan kualitas produk saat tiba, risiko pengiriman, dan sisa masa simpan.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
        {{-- Kartu 1: Skor Kesegaran (Hijau Zamrud Gelap) --}}
        <div class="bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-950 border border-emerald-700/60 rounded-3xl p-6 flex flex-col h-full text-white shadow-sm">
            {{-- 1. Judul & Status --}}
            <div class="flex items-center justify-between gap-2 min-h-[28px]">
                <p class="text-sm font-bold text-emerald-200">Skor Kesegaran</p>
                @if($predictionAvailable && $qualityAtArrival !== null)
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $qualityStatusBadge }}">
                        {{ $qualityStatus }}
                    </span>
                @endif
            </div>

            {{-- 2. Angka Utama & Cincin Progres --}}
            <div class="flex items-center justify-between gap-4 mt-4 min-h-[60px]">
                <div>
                    @if($predictionAvailable && $qualityAtArrival !== null)
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-4xl font-black text-white tracking-tight">{{ number_format($qualityAtArrival, 0) }}</span>
                            <span class="text-sm font-bold text-emerald-200/80">/100</span>
                        </div>
                    @else
                        <p class="text-xl font-bold text-emerald-100">{{ $isStorageStability ? 'Penyimpanan Kering' : 'Belum Tersedia' }}</p>
                    @endif
                </div>

                <div class="relative w-14 h-14 shrink-0">
                    <svg class="w-14 h-14 -rotate-90" viewBox="0 0 80 80">
                        <defs>
                            <linearGradient id="freshnessRingGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#a7f3d0" />
                                <stop offset="100%" stop-color="#34d399" />
                            </linearGradient>
                        </defs>
                        <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="7" class="text-emerald-950/80" />
                        @if($predictionAvailable && $qualityAtArrival !== null)
                            <circle cx="40" cy="40" r="34" fill="none" stroke="url(#freshnessRingGrad)" stroke-width="7"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $dashOffset }}"
                                    class="transition-all duration-700" />
                        @endif
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- 3. Progress Bar & 4. Keterangan Singkat --}}
            @if($predictionAvailable && $qualityAtArrival !== null)
                <div class="mt-4 h-3 rounded-full bg-emerald-950/90 border border-emerald-800/60 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-200 via-emerald-300 to-emerald-400 transition-all duration-700"
                         style="width: {{ max(0, min(100, $freshnessVal)) }}%;"></div>
                </div>
                <p class="text-xs text-emerald-100/80 leading-relaxed mt-2.5">
                    Skor menunjukkan perkiraan kondisi kesegaran produk. Semakin tinggi nilainya, semakin baik kondisi produk.
                </p>
            @endif

            {{-- 5. Garis Pemisah & 6. Penjelasan Kondisi --}}
            <div class="mt-auto pt-4">
                <p class="text-sm text-emerald-50/90 leading-relaxed pt-4 border-t border-emerald-800/70">
                    {{ $freshnessExplanation }}
                </p>
            </div>
        </div>

        {{-- Kartu 2: Tingkat Risiko Pengiriman (Biru Navy Gelap) --}}
        <div class="bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 border border-blue-800/60 rounded-3xl p-6 flex flex-col h-full text-white shadow-sm">
            {{-- 1. Judul & Badge --}}
            <div class="flex items-center justify-between gap-2 min-h-[28px]">
                <p class="text-sm font-bold text-blue-200">Tingkat Risiko Pengiriman</p>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $riskTone['badge'] }}">
                    {{ $riskDisplayLevel }}
                </span>
            </div>

            {{-- 2. Angka Utama & Satuan --}}
            <div class="flex items-center mt-4 min-h-[60px]">
                <div class="flex items-baseline flex-wrap gap-x-1.5 gap-y-0.5">
                    <span class="text-4xl font-black text-white tracking-tight">{{ $riskIndex }}</span>
                    <span class="text-sm font-bold text-blue-200/80">/100 Indeks Risiko Operasional</span>
                </div>
            </div>

            {{-- 3. Progress Bar & 4. Keterangan Singkat --}}
            <div class="mt-4 h-3 rounded-full bg-slate-900/90 border border-blue-900/60 overflow-hidden">
                <div class="h-full rounded-full {{ $riskTone['bar'] }} transition-all duration-700" style="width: {{ max(4, min(100, $riskIndex)) }}%;"></div>
            </div>
            <p class="text-xs text-blue-100/80 leading-relaxed mt-2.5">
                Indeks menunjukkan tingkat risiko operasional pengiriman. Semakin rendah nilainya, semakin aman kondisi pengiriman.
            </p>

            {{-- 5. Garis Pemisah & 6. Penjelasan Kondisi --}}
            <div class="mt-auto pt-4">
                <p class="text-sm text-blue-50/90 leading-relaxed pt-4 border-t border-blue-900/70">
                    {{ $riskTone['desc'] }}
                </p>
            </div>
        </div>

        {{-- Kartu 3: Sisa Masa Simpan (Teal Gelap) --}}
        <div class="bg-gradient-to-br from-teal-950 via-teal-900 to-cyan-950 border border-teal-700/60 rounded-3xl p-6 flex flex-col h-full text-white shadow-sm">
            {{-- 1. Judul & Ikon --}}
            <div class="flex items-center justify-between gap-2 min-h-[28px]">
                <p class="text-sm font-bold text-teal-200">Sisa Masa Simpan</p>
                <svg class="w-5 h-5 text-teal-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            {{-- 2. Angka Utama & Satuan --}}
            <div class="flex items-center mt-4 min-h-[60px]">
                <div class="flex items-baseline flex-wrap gap-x-1.5 gap-y-0.5">
                    <span class="text-4xl font-black text-white tracking-tight">
                        {{ $displayShelfLife !== null ? number_format((float) $displayShelfLife, 1, ',', '.') : '—' }}
                    </span>
                    <span class="text-sm font-bold text-teal-200/85">Hari Saat Tiba</span>
                </div>
            </div>

            {{-- 3. Progress Bar & 4. Keterangan Singkat --}}
            <div class="mt-4 h-3 rounded-full bg-teal-950/90 border border-teal-800/60 overflow-hidden">
                <div class="h-full rounded-full {{ $shelfLifeTone['bar'] }} transition-all duration-700" style="width: {{ $shelfLifeTone['pct'] }}%;"></div>
            </div>
            <p class="text-xs text-teal-100/80 leading-relaxed mt-2.5">
                Perkiraan sisa waktu layak simpan setelah produk tiba di tujuan berdasarkan rencana perjalanan.
            </p>

            {{-- 5. Garis Pemisah & 6. Penjelasan Kondisi --}}
            <div class="mt-auto pt-4">
                <p class="text-sm text-teal-50/90 leading-relaxed pt-4 border-t border-teal-800/70">
                    {{ $shelfLifeTone['desc'] }}
                </p>
            </div>
        </div>
    </div>

    @if(!empty($qualityPrediction['expiry_constraint_applied']) && !empty($qualityPrediction['shelf_life_reconciliation_message']))
        <div class="mt-4 bg-amber-50 border border-amber-300 rounded-2xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm text-amber-950 leading-relaxed">
                <strong class="font-extrabold text-amber-900">Peringatan Kedaluwarsa:</strong> {{ $qualityPrediction['shelf_life_reconciliation_message'] }}
            </p>
        </div>
    @endif
</section>