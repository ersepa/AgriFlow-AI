@php
    $recommendationPlan = session('recommendation_plan', []);
    $recommendedActions = $recommendationPlan['actions'] ?? session('recommended_actions', []);

    // Helper penyederhanaan bahasa tampilan (tanpa mengubah keputusan atau makna asli backend)
    $simplifyActionText = function (?string $text): string {
        $clean = trim((string) $text);
        if ($clean === '') return '';

        $map = [
            'Utamakan pengiriman ini dibanding muatan berisiko rendah dan periksa ulang dalam 24 jam' => 'Utamakan pengiriman ini dan periksa kembali kondisinya dalam 24 jam.',
            'Jaga batas masa simpan tercatat dari penundaan tambahan' => 'Hindari penundaan agar produk tetap layak disimpan.',
            'Pertahankan penanganan dengan kontrol suhu hingga tiba di tujuan' => 'Jaga suhu penyimpanan tetap sesuai selama perjalanan hingga produk tiba.',
            'Review shipment' => 'Tinjau kondisi pengiriman ini.',
            'Monitor shipment' => 'Pantau kondisi pengiriman secara berkala.',
        ];

        return $map[$clean] ?? $clean;
    };

    // Variasi warna gelap elegan untuk 3 kartu rekomendasi
    $cardThemes = [
        0 => [
            'card' => 'bg-emerald-950 border-emerald-700/70',
            'accentBar' => 'bg-emerald-400',
            'numBox' => 'bg-emerald-400/20 text-emerald-300 border-emerald-400/30',
            'label' => 'text-emerald-300',
            'desc' => 'text-emerald-50/90',
            'divider' => 'border-emerald-800/80',
            'timeLabel' => 'text-emerald-300/80',
            'timeValue' => 'text-emerald-200',
        ],
        1 => [
            'card' => 'bg-slate-950 border-blue-800/70',
            'accentBar' => 'bg-blue-400',
            'numBox' => 'bg-blue-400/20 text-blue-300 border-blue-400/30',
            'label' => 'text-blue-300',
            'desc' => 'text-blue-50/90',
            'divider' => 'border-blue-900/80',
            'timeLabel' => 'text-blue-300/80',
            'timeValue' => 'text-blue-200',
        ],
        2 => [
            'card' => 'bg-teal-950 border-teal-700/70',
            'accentBar' => 'bg-teal-400',
            'numBox' => 'bg-teal-400/20 text-teal-300 border-teal-400/30',
            'label' => 'text-teal-300',
            'desc' => 'text-teal-50/90',
            'divider' => 'border-teal-800/80',
            'timeLabel' => 'text-teal-300/80',
            'timeValue' => 'text-teal-200',
        ],
    ];
@endphp

@if(!empty($recommendedActions))
    <section aria-label="Rekomendasi Tindakan" class="pt-6 border-t border-slate-200 space-y-6">
        {{-- Header Seksi --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-emerald-700 uppercase tracking-wider">
                    Mesin Rekomendasi Tindakan
                </p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                    Rekomendasi Tindakan
                </h3>
                <p class="text-sm text-slate-600 mt-1.5 max-w-2xl leading-relaxed">
                    Langkah praktis yang disusun berdasarkan kondisi produk, tingkat risiko, sisa waktu perjalanan, dan standar penanganan komoditas.
                </p>
            </div>

            <div class="sm:text-right rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-2.5 shrink-0 w-fit">
                <p class="text-[11px] uppercase font-extrabold text-emerald-800 tracking-wider">
                    Batas Waktu Tindakan
                </p>
                <p class="text-sm font-black text-slate-900 mt-0.5">
                    {{ $recommendationPlan['action_window'] ?? session('dispatch_deadline', 'Fleksibel') }}
                </p>
            </div>
        </div>

        {{-- 3 Kartu Rekomendasi Berwarna Gelap Elegan --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach($recommendedActions as $index => $action)
                @php
                    $theme = $cardThemes[$index % 3];
                @endphp
                <div class="{{ $theme['card'] }} border rounded-2xl p-5 sm:p-6 relative overflow-hidden flex flex-col justify-between shadow-sm">
                    <div class="absolute top-0 left-0 w-full h-1 {{ $theme['accentBar'] }}"></div>

                    <div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl border {{ $theme['numBox'] }} text-sm font-black shrink-0">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="text-xs uppercase tracking-wider font-extrabold {{ $theme['label'] }}">
                                {{ $action['label'] ?? 'Tindakan' }}
                            </span>
                        </div>

                        <h4 class="text-base font-black text-white mt-4 leading-snug">
                            {{ $simplifyActionText($action['action'] ?? 'Tinjau kondisi pengiriman') }}
                        </h4>

                        @if(!empty($action['reason']))
                            <p class="text-sm {{ $theme['desc'] }} leading-relaxed mt-3">
                                {{ $action['reason'] }}
                            </p>
                        @endif
                    </div>

                    @if(!empty($action['window']))
                        <div class="mt-5 pt-3.5 border-t {{ $theme['divider'] }} flex items-center justify-between gap-3">
                            <span class="text-xs font-bold {{ $theme['timeLabel'] }}">
                                Waktu Pelaksanaan
                            </span>
                            <span class="text-xs font-extrabold {{ $theme['timeValue'] }} text-right">
                                {{ $action['window'] }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Alasan Rekomendasi & Hasil yang Diharapkan (Latar Lembut, Teks Gelap Kontras Tinggi) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-indigo-50/90 border border-indigo-200 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <p class="text-xs uppercase font-extrabold text-indigo-900 tracking-wider">
                        Alasan Rekomendasi
                    </p>
                </div>
                <p class="text-sm sm:text-[15px] text-slate-800 leading-relaxed font-medium">
                    {{ $recommendationPlan['decision_rationale'] ?? session('decision_rationale', 'Lanjutkan pemantauan kondisi operasional saat ini.') }}
                </p>
            </div>

            <div class="bg-emerald-50/90 border border-emerald-200 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <p class="text-xs uppercase font-extrabold text-emerald-900 tracking-wider">
                        Hasil yang Diharapkan
                    </p>
                </div>
                <p class="text-sm sm:text-[15px] text-slate-800 leading-relaxed font-medium">
                    {{ $recommendationPlan['expected_outcome'] ?? session('expected_outcome', 'Lanjutkan pemantauan pengiriman sesuai batas operasional saat ini.') }}
                </p>
            </div>
        </div>

        {{-- Panduan Penanganan Komoditas (Accordion) --}}
        <details class="group rounded-2xl bg-teal-50/70 border border-teal-200 overflow-hidden shadow-2xs">
            <summary class="cursor-pointer list-none flex items-center justify-between gap-4 px-5 py-4 hover:bg-teal-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-black text-slate-900">
                            Panduan Penanganan Komoditas
                        </span>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Standar penyimpanan, suhu, kelembapan, dan transportasi yang disarankan.
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-xl bg-white border border-teal-200 text-teal-800 text-xs font-extrabold shrink-0">
                    <span class="group-open:hidden">Buka Panduan +</span>
                    <span class="hidden group-open:inline">Tutup Panduan −</span>
                </span>
            </summary>

            <div class="px-5 pb-5 pt-4 border-t border-teal-200/80 space-y-4">
                @if(($recommendationPlan['quality_model_type'] ?? null) === 'storage_stability')
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Model Penyimpanan</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                Stabilitas Komoditas Kering
                            </p>
                        </div>

                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Batas Kadar Air</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                @if(($recommendationPlan['safe_moisture_short_term_max_percent'] ?? null) !== null)
                                    ≤ {{ number_format($recommendationPlan['safe_moisture_short_term_max_percent'], 1, ',', '.') }}%
                                    @if(
                                        ($recommendationPlan['safe_moisture_long_term_max_percent'] ?? null) !== null
                                        && $recommendationPlan['safe_moisture_long_term_max_percent']
                                            != $recommendationPlan['safe_moisture_short_term_max_percent']
                                    )
                                        · jangka panjang ≤ {{ number_format($recommendationPlan['safe_moisture_long_term_max_percent'], 1, ',', '.') }}%
                                    @endif
                                @else
                                    Belum tersedia
                                @endif
                            </p>
                        </div>

                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Panduan Kelembapan</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                @if(($recommendationPlan['safe_relative_humidity_max_percent'] ?? null) !== null)
                                    ≤ {{ number_format($recommendationPlan['safe_relative_humidity_max_percent'], 0, ',', '.') }}%
                                @else
                                    Belum tersedia
                                @endif
                            </p>
                        </div>

                        <div class="bg-white border border-amber-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Status Data Kondisi</p>
                            <p class="text-sm font-extrabold text-amber-800 mt-1.5">
                                {{ $recommendationPlan['storage_evidence_status'] ?? 'Data kondisi belum tersedia' }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                        <p class="text-xs font-bold text-slate-500">Panduan Transportasi</p>
                        <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                            {{ $recommendationPlan['recommended_vehicle'] ?? 'Jaga muatan tetap kering dan terlindung dari kelembapan.' }}
                        </p>

                        @if(!empty($recommendationPlan['storage_science_note']))
                            <p class="text-xs text-slate-600 leading-relaxed mt-2.5">
                                {{ $recommendationPlan['storage_science_note'] }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Transportasi</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                {{ $recommendationPlan['recommended_vehicle'] ?? 'Gunakan kondisi pengangkutan yang sesuai.' }}
                            </p>
                        </div>

                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Suhu Acuan</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                {{ $recommendationPlan['recommended_temperature_range'] ?? 'Belum tersedia' }}
                            </p>
                        </div>

                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Kelembapan Acuan</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                {{ $recommendationPlan['recommended_humidity_range'] ?? 'Belum tersedia' }}
                            </p>
                        </div>

                        <div class="bg-white border border-teal-200/80 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500">Batas Risiko Dingin</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1.5">
                                {{ $recommendationPlan['chilling_threshold'] ?? 'Belum ada batas tercatat' }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="bg-white border border-teal-200/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-500">Profil Komoditas Acuan</p>
                        <p class="text-sm font-extrabold text-slate-900 mt-0.5">
                            {{ $recommendationPlan['commodity_profile_name'] ?? 'Profil komoditas belum tersedia' }}
                        </p>
                    </div>

                    @if(!empty($recommendationPlan['reference_source_url']))
                        <a href="{{ $recommendationPlan['reference_source_url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-extrabold text-teal-700 hover:text-teal-900 hover:underline uppercase tracking-wider">
                            {{ $recommendationPlan['reference_source_name'] ?? 'Lihat Sumber Referensi' }} →
                        </a>
                    @endif
                </div>
            </div>
        </details>
    </section>
@endif