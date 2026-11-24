@php
    $riskAssessment = session('risk_assessment', []);
    $qualityPrediction = session('quality_prediction', []);
    $recommendationPlan = session('recommendation_plan', []);
    $limitations = $riskAssessment['limitations'] ?? [];
    $hasAdvancedNotes = !empty($limitations)
        || !empty($riskAssessment['critical_override_applied'])
        || ($qualityPrediction['prediction_available'] ?? false);

    // Penyederhanaan bahasa catatan keterbatasan model (hanya lapisan tampilan, tanpa mengubah makna ilmiah)
    $simplifyLimitation = function (string $text): string {
        $clean = trim($text);

        $map = [
            'Skor ini adalah indeks risiko operasional, bukan probabilitas pembusukan statistik.' => 'Skor ini menunjukkan tingkat risiko pengiriman, bukan persentase kemungkinan produk membusuk.',
            'The score is an operational risk index, not a spoilage probability.' => 'Skor ini menunjukkan tingkat risiko pengiriman, bukan persentase kemungkinan produk membusuk.',
            'Model bersifat deterministik dan bukan hasil pelatihan machine learning.' => 'Penilaian ini menggunakan aturan dan perhitungan yang telah ditentukan, bukan hasil pelatihan machine learning.',
            'The model is deterministic and has not been statistically calibrated against outcome labels.' => 'Penilaian ini menggunakan aturan dan perhitungan yang telah ditentukan, bukan hasil pelatihan machine learning.',
            'Data sensor kontinu, kondisi kemasan, kerusakan mekanis, atmosfer, dan mikroba belum termasuk dalam perhitungan.' => 'Perhitungan ini belum mencakup pemantauan sensor secara terus-menerus, kondisi kemasan, kerusakan fisik, kondisi udara, dan mikroorganisme.',
            'Continuous cargo sensor time-series, packaging condition, mechanical damage, atmosphere, and microbial measurements are not yet included.' => 'Perhitungan ini belum mencakup pemantauan sensor secara terus-menerus, kondisi kemasan, kerusakan fisik, kondisi udara, dan mikroorganisme.',
            'Paparan suhu menggunakan nilai skenario Digital Twin jika diisi, atau suhu pengiriman tercatat jika tersedia.' => 'Penilaian suhu menggunakan data dari simulasi Digital Twin jika tersedia. Jika tidak, sistem menggunakan catatan suhu pengiriman yang ada.',
            'Temperature exposure uses an explicit Digital Twin scenario override when provided, otherwise the recorded shipment temperature is used; missing condition evidence remains explicit.' => 'Penilaian suhu menggunakan data dari simulasi Digital Twin jika tersedia. Jika tidak, sistem menggunakan catatan suhu pengiriman yang ada.',
            'Untuk komoditas kering, data kadar air/kelembapan yang belum tercatat ditampilkan sebagai keterbatasan data, bukan skor kesegaran buatan.' => 'Untuk komoditas kering, jika data kadar air atau kelembapan belum tersedia, sistem akan menandainya sebagai data yang belum tercatat dan tidak membuat skor kesegaran sendiri.',
            'For dry commodities, missing recorded moisture/RH condition is represented as evidence uncertainty rather than a fabricated arrival-quality score.' => 'Untuk komoditas kering, jika data kadar air atau kelembapan belum tersedia, sistem akan menandainya sebagai data yang belum tercatat dan tidak membuat skor kesegaran sendiri.',
        ];

        return $map[$clean] ?? $clean;
    };
@endphp

<div class="bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 border border-emerald-200 rounded-2xl p-5 sm:p-6 shadow-2xs">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>

        <div class="flex-1 min-w-0">
            <h4 class="text-lg sm:text-xl font-black text-emerald-950">Tentang Analisis Ini</h4>
            <p class="text-sm sm:text-[15px] text-slate-700 leading-relaxed mt-1.5">
                AgriFlow menilai kondisi pengiriman berdasarkan informasi hasil panen, masa simpan, suhu, dan perjalanan yang tersedia.
                Hasil penilaian ini digunakan sebagai gambaran tingkat risiko operasional, bukan jaminan bahwa produk pertanian pasti akan rusak.
            </p>

            @if($hasAdvancedNotes)
                <details class="mt-4 group/notes">
                    <summary class="cursor-pointer list-none inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-emerald-200 text-sm font-bold text-emerald-800 hover:bg-emerald-50 hover:border-emerald-300 transition-colors shadow-2xs">
                        <span>Lihat dasar dan perhitungan analisis</span>
                        <svg class="w-4 h-4 text-emerald-600 group-open/notes:rotate-180 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>

                    <div class="mt-5 pt-5 border-t border-emerald-200/70 space-y-5">
                        @if(!empty($riskAssessment['critical_override_applied']) && !empty($riskAssessment['critical_override_reason']))
                            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-900 leading-relaxed">
                                <strong class="font-extrabold text-rose-800">Aturan Pengaman Diterapkan:</strong> {{ $riskAssessment['critical_override_reason'] }}
                            </div>
                        @endif

                        {{-- 4 Kartu Metrik Berwarna Lembut --}}
                        @if($qualityPrediction['prediction_available'] ?? false)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                {{-- 1. Kondisi Produk Sebelum Dikirim (Hijau Muda) --}}
                                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-[13px] font-bold text-emerald-900 leading-snug">Kondisi Produk Sebelum Dikirim</span>
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-black text-emerald-950">
                                        {{ session('quality_at_departure') !== null ? number_format(session('quality_at_departure'), 0) . '/100' : 'Belum tersedia' }}
                                    </p>
                                </div>

                                {{-- 2. Perkiraan Penurunan Kualitas Selama Perjalanan (Oranye Muda) --}}
                                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-[13px] font-bold text-amber-900 leading-snug">Perkiraan Penurunan Kualitas Selama Perjalanan</span>
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 17l-8.5-8.5-5 5L2 7"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 17h6v-6"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-black text-amber-950">
                                        {{ session('quality_loss_during_transit') !== null ? number_format(session('quality_loss_during_transit'), 0) . ' poin' : 'Belum tersedia' }}
                                    </p>
                                </div>

                                {{-- 3. Batas Waktu Aman Pengiriman (Biru Muda) --}}
                                <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-[13px] font-bold text-blue-900 leading-snug">Batas Waktu Aman Pengiriman</span>
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-black text-blue-950">
                                        {{ session('safe_transit_window_hours') !== null ? number_format((float) session('safe_transit_window_hours'), 1, ',', '.') . ' jam' : 'Belum tersedia' }}
                                    </p>
                                </div>

                                {{-- 4. Perkiraan Masa Simpan Awal (Teal Muda) --}}
                                <div class="rounded-xl bg-teal-50 border border-teal-200 p-4 flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-[13px] font-bold text-teal-900 leading-snug">Perkiraan Masa Simpan Awal</span>
                                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-black text-teal-950">
                                        {{ isset($qualityPrediction['baseline_shelf_life_days']) ? number_format((float) $qualityPrediction['baseline_shelf_life_days'], 1, ',', '.') . ' hari' : 'Belum tersedia' }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Catatan Penjelasan Model --}}
                        @if(!empty($limitations))
                            <div class="rounded-xl bg-white/90 border border-emerald-200/80 p-4">
                                <p class="text-[13px] font-extrabold uppercase tracking-wider text-emerald-800 mb-2.5">Catatan &amp; Batasan Perhitungan</p>
                                <ul class="space-y-2 text-sm text-slate-700 leading-relaxed">
                                    @foreach($limitations as $limitation)
                                        <li class="flex items-start gap-2.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-2 shrink-0"></span>
                                            <span>{{ $simplifyLimitation((string) $limitation) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Sumber Acuan --}}
                        @if(!empty($recommendationPlan['reference_source_url']))
                            <div class="pt-3 border-t border-emerald-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
                                <span class="text-slate-600">
                                    Sumber Acuan: <strong class="text-slate-900">{{ $recommendationPlan['commodity_profile_name'] ?? 'Profil Komoditas' }}</strong>
                                </span>
                                <a href="{{ $recommendationPlan['reference_source_url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 font-extrabold text-emerald-700 hover:text-emerald-900 hover:underline">
                                    {{ $recommendationPlan['reference_source_name'] ?? 'Lihat Sumber' }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </details>
            @endif
        </div>
    </div>
</div>