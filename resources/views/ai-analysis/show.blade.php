<x-app-layout>
    @php
        /*
         * $analysisRecord = persisted AiAnalysis selected from history.
         * $decisionAnalysis = immutable snapshot payload selected from history.
         */
        $analysisRecord = $analysisRecord ?? $shipment->aiAnalyses->sortByDesc('created_at')->first();
        $isHistoricalSnapshot = $isHistoricalSnapshot ?? true;
        $snapshotMeta = $snapshotMeta ?? [];

        $qualityPrediction = $decisionAnalysis['quality_prediction'] ?? [];
        $predictionAvailable = $qualityPrediction['prediction_available'] ?? false;
        $expiryConstraintApplied = $qualityPrediction['expiry_constraint_applied'] ?? false;
        $reconciliationStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? null;
        $reconciliationMessage = $qualityPrediction['shelf_life_reconciliation_message'] ?? null;

        $arrivalQuality = $decisionAnalysis['quality_at_arrival'] ?? null;
        $departureQuality = $decisionAnalysis['quality_at_departure'] ?? null;
        $remainingShelfLife = $decisionAnalysis['predicted_remaining_shelf_life_days'] ?? null;
        $safeTransitWindow = $decisionAnalysis['safe_transit_window_hours'] ?? null;
        $temperatureAssessment = $decisionAnalysis['temperature_assessment'] ?? [];
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
        $temperatureC = $shipment->recorded_temperature_c ?? ($qualityPrediction['temperature_c'] ?? null);
        $humidityPercent = $shipment->recorded_relative_humidity_percent ?? null;
        $temperatureBasis = $qualityPrediction['temperature_basis'] ?? 'unknown';
        $dataConfidence = $decisionAnalysis['data_confidence'] ?? 0;
        $commodityProfile = $decisionAnalysis['commodity_profile'] ?? [];

        $isStorageStability =
            ($commodityProfile['quality_model_type'] ?? null) === 'storage_stability'
            || !empty($qualityPrediction['storage_stability_reference_available']);

        $riskLevel = $analysisRecord?->risk_level ?? ($decisionAnalysis['risk_level'] ?? 'Low');
        $riskKey = strtolower(trim((string) $riskLevel));
        $riskScore = $decisionAnalysis['risk_score'] ?? null;
        $readinessScore = $decisionAnalysis['operational_readiness_score'] ?? ($analysisRecord?->sustainability_score ?? null);

        // Referensi suhu & kelembapan dari profil komoditas historis
        $optTempMin = $commodityProfile['optimal_temp_min'] ?? null;
        $optTempMax = $commodityProfile['optimal_temp_max'] ?? null;
        $optHumMin = $commodityProfile['optimal_humidity_min'] ?? null;
        $optHumMax = $commodityProfile['optimal_humidity_max'] ?? null;

        // Format angka Indonesia (koma desimal, titik ribuan)
        $fmtNum = function ($val, int $decimals = 1): string {
            $num = (float) $val;
            $formatted = number_format($num, $decimals, ',', '.');
            return $decimals > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
        };

        // Format acuan suhu & kelembapan yang aman (target tunggal vs rentang)
        $formatReference = function ($min, $max, string $unit, string $singlePrefix, string $rangePrefix) use ($fmtNum): array {
            $hasMin = is_numeric($min);
            $hasMax = is_numeric($max);

            if ($hasMin && $hasMax) {
                $minF = (float) $min;
                $maxF = (float) $max;
                if ($minF === $maxF) {
                    $val = $fmtNum($minF, 1) . $unit;
                    return ['valid' => true, 'full' => "{$singlePrefix}: {$val}"];
                }
                if ($minF < $maxF) {
                    $val = $fmtNum($minF, 1) . '–' . $fmtNum($maxF, 1) . $unit;
                    return ['valid' => true, 'full' => "{$rangePrefix}: {$val}"];
                }
                return ['valid' => false, 'full' => 'Data acuan belum tersedia.'];
            }

            if ($hasMin || $hasMax) {
                $single = $hasMin ? (float) $min : (float) $max;
                $val = $fmtNum($single, 1) . $unit;
                return ['valid' => true, 'full' => "{$singlePrefix}: {$val}"];
            }

            return ['valid' => false, 'full' => 'Data acuan belum tersedia.'];
        };

        $tempRef = $formatReference($optTempMin, $optTempMax, '°C', 'Suhu acuan', 'Kisaran suhu yang disarankan');
        $humRef = $formatReference($optHumMin, $optHumMax, '%', 'Kelembapan acuan', 'Kisaran kelembapan yang disarankan');

        // Format tanggal Bahasa Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanSingkat = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatDateIndo = function ($date, bool $withTime = false, bool $fullMonth = false) use ($bulanIndo, $bulanSingkat): string {
            if (!$date) return 'Tidak tersedia';
            $c = \Carbon\Carbon::parse($date);
            $monthName = $fullMonth
                ? ($bulanIndo[(int) $c->format('n')] ?? $c->format('F'))
                : ($bulanSingkat[(int) $c->format('n')] ?? $c->format('M'));
            $base = $c->format('d') . ' ' . $monthName . ' ' . $c->format('Y');
            return $withTime ? $base . ' · ' . $c->format('H:i') : $base;
        };

        $analyzedAt = $analysisRecord?->created_at ?? ($snapshotMeta['captured_at'] ?? null);

        // Gaya lencana tingkat risiko
        $riskStyle = match (true) {
            str_contains($riskKey, 'critical') => [
                'label' => 'Risiko Kritis',
                'badge' => 'bg-rose-600 text-white border-rose-700',
            ],
            str_contains($riskKey, 'high') => [
                'label' => 'Risiko Tinggi',
                'badge' => 'bg-orange-500 text-white border-orange-600',
            ],
            str_contains($riskKey, 'medium') || str_contains($riskKey, 'moderate') => [
                'label' => 'Risiko Sedang',
                'badge' => 'bg-amber-500 text-white border-amber-600',
            ],
            default => [
                'label' => 'Risiko Rendah',
                'badge' => 'bg-emerald-600 text-white border-emerald-700',
            ],
        };

        // Status kondisi produk berdasarkan skor historis
        if ($predictionAvailable && $arrivalQuality !== null) {
            if ($arrivalQuality >= 70) {
                $conditionLabel = 'Kondisi Baik';
                $conditionTheme = [
                    'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'bar' => 'bg-gradient-to-r from-emerald-500 to-teal-500',
                    'score' => 'text-emerald-700',
                ];
            } elseif ($arrivalQuality >= 50) {
                $conditionLabel = 'Perlu Perhatian';
                $conditionTheme = [
                    'badge' => 'bg-amber-100 text-amber-900 border-amber-300',
                    'bar' => 'bg-gradient-to-r from-amber-400 to-orange-500',
                    'score' => 'text-amber-700',
                ];
            } else {
                $conditionLabel = 'Berisiko';
                $conditionTheme = [
                    'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                    'bar' => 'bg-gradient-to-r from-rose-500 to-red-600',
                    'score' => 'text-rose-700',
                ];
            }
        } else {
            $conditionLabel = $isStorageStability ? 'Model Penyimpanan Kering' : 'Belum Tersedia';
            $conditionTheme = [
                'badge' => 'bg-slate-100 text-slate-700 border-slate-300',
                'bar' => 'bg-slate-300',
                'score' => 'text-slate-700',
            ];
        }

        // Validasi suhu & kelembapan terhadap acuan
        $tempInRange = null;
        if (is_numeric($temperatureC) && is_numeric($optTempMin) && is_numeric($optTempMax) && (float) $optTempMin <= (float) $optTempMax) {
            $tempInRange = ((float) $temperatureC >= (float) $optTempMin && (float) $temperatureC <= (float) $optTempMax);
        } elseif ($temperatureStatus === 'Optimal') {
            $tempInRange = true;
        } elseif (in_array($temperatureStatus, ['Chilling risk', 'Suboptimal', 'High risk', 'Outside reference'], true)) {
            $tempInRange = false;
        }

        $humInRange = null;
        if (is_numeric($humidityPercent) && is_numeric($optHumMin) && is_numeric($optHumMax) && (float) $optHumMin <= (float) $optHumMax) {
            $humInRange = ((float) $humidityPercent >= (float) $optHumMin && (float) $humidityPercent <= (float) $optHumMax);
        }

        // Temuan utama (Hal yang Perlu Diperhatikan)
        $findings = [];

        if ($expiryConstraintApplied) {
            $findings[] = [
                'tone' => 'amber',
                'title' => 'Batas Kesegaran Produk',
                'desc' => match ($reconciliationStatus) {
                    'Recorded expiry is limiting' => 'Batas tanggal kedaluwarsa membatasi sisa waktu penyimpanan produk. Pastikan pengiriman tiba tepat waktu agar mutu tetap terjaga.',
                    'Reference shelf life is limiting' => 'Batas masa simpan standar komoditas lebih ketat daripada tanggal kedaluwarsa, sehingga kesegaran perlu dipantau lebih ketat.',
                    default => 'Produk memiliki batas waktu penyimpanan yang perlu diperhatikan agar kualitasnya tetap terjaga hingga tujuan.',
                },
            ];
        }

        if ($tempInRange === false || $temperatureStatus === 'Chilling risk') {
            $findings[] = [
                'tone' => $temperatureStatus === 'Chilling risk' ? 'rose' : 'amber',
                'title' => $temperatureStatus === 'Chilling risk' ? 'Risiko Kerusakan Dingin' : 'Suhu Di Luar Batas Acuan',
                'desc' => $tempRef['valid']
                    ? 'Suhu saat analisis (' . $fmtNum($temperatureC, 1) . '°C) berada di luar ' . strtolower($tempRef['full']) . '.'
                    : 'Suhu penyimpanan saat analisis perlu disesuaikan agar kesegaran produk tidak cepat menurun.',
            ];
        }

        if ($humInRange === false) {
            $findings[] = [
                'tone' => 'amber',
                'title' => 'Kelembapan Perlu Diperhatikan',
                'desc' => $humRef['valid']
                    ? 'Kelembapan tercatat (' . $fmtNum($humidityPercent, 0) . '%) berada di luar ' . strtolower($humRef['full']) . '.'
                    : 'Kelembapan saat analisis berada di luar rentang ideal untuk komoditas ini.',
            ];
        }

        if (is_numeric($arrivalQuality) && $arrivalQuality < 50) {
            $findings[] = [
                'tone' => 'rose',
                'title' => 'Perkiraan Kualitas Saat Tiba Rendah',
                'desc' => 'Skor kondisi saat tiba tercatat rendah (' . number_format((float) $arrivalQuality, 0) . '/100), sehingga berisiko menurunkan mutu produk.',
            ];
        }

        // Lokalisasi teks dinamis AI ke Bahasa Indonesia alami (tanpa mengubah data historis)
        $localizeAiText = function (?string $text): string {
            $clean = trim((string) $text);
            if ($clean === '') return '';

            $exactTranslations = [
                'Maintain current route and monitor freshness margin.' => 'Tetap gunakan rute saat ini dan pantau kesegaran produk selama perjalanan.',
                'Continue monitoring the shipment against its current operational constraints.' => 'Lanjutkan pemantauan pengiriman sesuai batas operasional saat ini.',
                'Preserve the current operational window and reassess when recorded conditions change.' => 'Pertahankan jadwal pengiriman saat ini dan evaluasi ulang jika kondisi penyimpanan berubah.',
                'Review shipment' => 'Tinjau kembali kondisi dan rencana pengiriman ini.',
                'Prioritize dispatch and monitor temperature closely.' => 'Utamakan jadwal keberangkatan dan pantau suhu penyimpanan secara ketat.',
                'Prioritize dispatch and re-evaluate within 24 hours.' => 'Utamakan pengiriman ini dan periksa kembali kondisinya dalam 24 jam.',
                'Process ahead of lower-risk cargo and review within 24 hours.' => 'Utamakan pengiriman ini dan periksa kembali kondisinya dalam 24 jam.',
                'Expedite delivery and verify cold-chain temperature control.' => 'Percepat pengiriman dan pastikan pengaturan suhu pendingin bekerja dengan baik.',
                'Verify recorded cargo temperature and relative humidity.' => 'Periksa kembali suhu dan kelembapan produk yang tercatat.',
                'Keep the current shipment plan and continue standard monitoring.' => 'Pertahankan rencana pengiriman saat ini dan lanjutkan pemantauan rutin.',
            ];

            if (isset($exactTranslations[$clean])) {
                return $exactTranslations[$clean];
            }

            // Pola kalimat dinamis (termasuk kalimat campuran)
            $regexRules = [
                '/^Process ahead of lower-risk cargo and review (?:within|dalam)\s+(\d+)\s*(?:hours?|jam)\.?$/i' => 'Utamakan pengiriman ini dan periksa kembali kondisinya dalam $1 jam.',
                '/^Prioritize (?:dispatch|shipment|delivery|this shipment)(?: and)?\s*(?:re-?check|re-?evaluate|review)(?: its condition)? (?:within|dalam)\s+(\d+)\s*(?:hours?|jam)\.?$/i' => 'Utamakan pengiriman ini dan periksa kembali kondisinya dalam $1 jam.',
                '/^Re-?evaluate (?:shipment|conditions?) (?:within|dalam)\s+(\d+)\s*(?:hours?|jam)\.?$/i' => 'Periksa kembali kondisi pengiriman dalam $1 jam.',
                '/^Adjust (?:storage|cargo|cooling) temperature toward ([\d\.,\-–]+)\s*°?C\.?$/i' => 'Sesuaikan suhu penyimpanan mendekati $1°C.',
                '/^Monitor (?:cargo|shipment) temperature and (?:relative )?humidity during transit\.?$/i' => 'Pantau suhu dan kelembapan produk selama proses pengiriman.',
            ];

            foreach ($regexRules as $pattern => $replacement) {
                if (preg_match($pattern, $clean)) {
                    return preg_replace($pattern, $replacement, $clean);
                }
            }

            // Penggantian frasa teknis agar tidak ada kalimat campur bahasa
            $phraseReplacements = [
                '/\bProcess ahead of lower-risk cargo and review\b/i' => 'Utamakan pengiriman ini dan periksa kembali kondisinya',
                '/\bOperational Risk Index\b/i' => 'tingkat risiko pengiriman',
                '/\bQuality-at-Arrival\b/i' => 'perkiraan kondisi saat tiba',
                '/\bquality at arrival\b/i' => 'perkiraan kondisi saat tiba',
                '/\bpredicted arrival quality\b/i' => 'perkiraan kondisi saat tiba',
                '/\bremaining operational life\b/i' => 'sisa waktu penyimpanan',
                '/\bremaining shelf life\b/i' => 'sisa masa simpan',
                '/\bsafe transit window\b/i' => 'waktu aman pengiriman',
                '/\bfreshness margin\b/i' => 'batas kesegaran produk',
                '/\bMaintain current route\b/i' => 'Tetap gunakan rute saat ini',
                '/\bmonitor freshness margin\b/i' => 'pantau kesegaran produk',
                '/\bModerate Risk\b/i' => 'Risiko Sedang',
                '/\bHigh Risk\b/i' => 'Risiko Tinggi',
                '/\bLow Risk\b/i' => 'Risiko Rendah',
                '/\bCritical Risk\b/i' => 'Risiko Kritis',
                '/\bmoderate operational risk\b/i' => 'risiko pengiriman tingkat sedang',
                '/\bhigh operational risk\b/i' => 'risiko pengiriman tingkat tinggi',
                '/\blow operational risk\b/i' => 'risiko pengiriman tingkat rendah',
                '/\brecorded cargo temperature\b/i' => 'suhu produk tercatat',
                '/\brecorded relative humidity\b/i' => 'kelembapan tercatat',
                '/\brecorded expiry\b/i' => 'batas kedaluwarsa tercatat',
                '/\boutside reference range\b/i' => 'di luar batas acuan',
                '/\bwithin reference range\b/i' => 'sesuai batas acuan',
                '/\bContinue monitoring\b/i' => 'Lanjutkan pemantauan',
                '/\bPreserve the current operational window\b/i' => 'Pertahankan jadwal pengiriman saat ini',
                '/\band reassess when recorded conditions change\b/i' => 'dan evaluasi ulang saat kondisi berubah',
                '/\bagainst its current operational constraints\b/i' => 'sesuai batas operasional saat ini',
                '/\bshipment\b/i' => 'pengiriman',
                '/\bwithin (\d+) hours?\b/i' => 'dalam $1 jam',
                '/\b(\d+(?:\.\d+)?) days?\b/i' => '$1 hari',
                '/\b(\d+(?:\.\d+)?) hours?\b/i' => '$1 jam',
            ];

            return trim((string) preg_replace(array_keys($phraseReplacements), array_values($phraseReplacements), $clean));
        };

        // Parsing rekomendasi, alasan penilaian, dan kesimpulan dari record historis
        $rawRecommendations = (string) ($analysisRecord?->recommendations ?? '');
        $parts = explode('Explanation:', $rawRecommendations);
        $recommendationText = trim(str_replace('Recommendations:', '', $parts[0] ?? ''));

        $rawRecList = $recommendationText !== ''
            ? array_values(array_filter(array_map(
                static fn (string $line): string => trim(preg_replace('/^\s*[-•]\s*/', '', $line)),
                preg_split('/\R/', $recommendationText) ?: []
            )))
            : [];

        $rawExplanation = isset($parts[1]) ? trim($parts[1]) : '';
        $rawConclusion = '';

        if ($rawExplanation !== '' && str_contains($rawExplanation, 'Conclusion:')) {
            [$expText, $conclText] = array_pad(explode('Conclusion:', $rawExplanation, 2), 2, '');
            $rawExplanation = trim($expText);
            $rawConclusion = trim($conclText);
        }

        if (empty($rawRecList) && filled($rawRecommendations)) {
            if (str_contains($rawRecommendations, ' — ')) {
                [$legacyAction, $legacyReason] = array_pad(explode(' — ', $rawRecommendations, 2), 2, '');
                $rawRecList = [trim($legacyAction)];
                $rawExplanation = trim($legacyReason);
            } else {
                $rawRecList = [$rawRecommendations];
            }
        }

        $recommendationsIndo = array_values(array_filter(array_map($localizeAiText, $rawRecList)));
        $primaryRecommendation = $recommendationsIndo[0] ?? 'Lanjutkan pemantauan kondisi pengiriman secara berkala.';
        $explanationIndo = $localizeAiText($rawExplanation);
        $conclusionIndo = $localizeAiText($rawConclusion);

        // Hindari pengulangan kesimpulan jika isinya sama dengan rekomendasi utama atau alasan penilaian
        $norm = fn (string $s): string => mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $s));
        $showConclusion = $conclusionIndo !== ''
            && $norm($conclusionIndo) !== $norm($primaryRecommendation)
            && $norm($conclusionIndo) !== $norm($explanationIndo);

        // Sumber data & status pengiriman dalam Bahasa Indonesia
        $rawSource = $shipment->condition_source ?: $temperatureBasis;
        $sourceIndo = match ($rawSource) {
            'manual_entry' => 'Input Manual',
            'recorded' => 'Data Tercatat',
            'ambient_fallback', 'estimated' => 'Estimasi Lingkungan',
            default => ucwords(str_replace('_', ' ', (string) $rawSource)),
        };

        $statusIndo = match ($shipment->status) {
            'Harvested' => 'Baru Dipanen',
            'Packed' => 'Siap Kirim (Dikemas)',
            'In Transit' => 'Dalam Perjalanan',
            'Delivered' => 'Selesai Dikirim',
            default => $shipment->status ?? 'Tidak diketahui',
        };
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-sky-50/70 to-emerald-50/60 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- ================= TOP BAR ================= --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('ai-analysis.history') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-indigo-700 hover:border-indigo-300 shadow-sm transition-all duration-200 hover:-translate-x-0.5 text-sm font-bold">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Riwayat Analisis
                </a>

                @if($isHistoricalSnapshot)
                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-800 text-xs font-extrabold shadow-sm">
                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Rekaman Historis Tersimpan
                    </span>
                @endif
            </div>

            {{-- =====================================================================
                 SECTION A: RINGKASAN ANALISIS (Soft Emerald-Teal Gradient)
            ===================================================================== --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100/80 via-emerald-50 to-cyan-50/90 border border-emerald-200 p-6 sm:p-8 shadow-md shadow-emerald-900/5">
                <div class="pointer-events-none absolute -top-20 -right-20 w-64 h-64 rounded-full bg-cyan-300/25 blur-3xl"></div>

                <div class="relative z-10 space-y-6">
                    {{-- Header Identity --}}
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/25 flex-shrink-0">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">Ringkasan Analisis</p>
                                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 capitalize tracking-tight mt-0.5">
                                    {{ $shipment->harvest?->commodity ?? 'Komoditas Tidak Diketahui' }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-semibold text-slate-600 mt-1.5">
                                    <span>ID Pengiriman: <strong class="text-slate-900">#{{ $shipment->id }}</strong></span>
                                    <span class="text-emerald-400">•</span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                        </svg>
                                        {{ $formatDateIndo($analyzedAt, true, true) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border text-sm font-extrabold shadow-sm {{ $riskStyle['badge'] }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                {{ $riskStyle['label'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Visual Origin-to-Destination Route --}}
                    <div class="rounded-2xl bg-white/95 backdrop-blur-sm border border-emerald-200/80 p-5 sm:p-6 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-7 items-center gap-4">
                            <div class="md:col-span-2 flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-500">Asal Pengiriman</p>
                                    <p class="text-xl font-black text-slate-900 capitalize truncate">{{ $shipment->origin }}</p>
                                </div>
                            </div>

                            <div class="md:col-span-3 flex flex-col items-center justify-center py-2">
                                <div class="w-full flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                    <div class="flex-1 border-t-2 border-dashed border-cyan-400"></div>
                                    <div class="px-3.5 py-2 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-900 inline-flex items-center gap-2 shadow-sm">
                                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2m10 0H9m10 0h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                                            <circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                                        </svg>
                                        <span class="text-sm font-black whitespace-nowrap">{{ number_format($shipment->distance_km ?? 0, 0, ',', '.') }} km</span>
                                        @if($shipment->duration_hours !== null)
                                            <span class="text-cyan-400">•</span>
                                            <span class="text-sm font-bold text-cyan-700 whitespace-nowrap">{{ $fmtNum($shipment->duration_hours, 1) }} jam</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 border-t-2 border-dashed border-cyan-400"></div>
                                    <span class="w-3 h-3 rounded-full bg-cyan-600 flex-shrink-0"></span>
                                </div>
                                <p class="text-xs font-semibold text-slate-500 mt-1.5">Jarak Pengiriman</p>
                            </div>

                            <div class="md:col-span-2 flex items-center md:justify-end gap-3.5 md:text-right">
                                <div class="min-w-0 order-2 md:order-1">
                                    <p class="text-xs font-bold text-slate-500">Tujuan Pengiriman</p>
                                    <p class="text-xl font-black text-slate-900 capitalize truncate">{{ $shipment->destination }}</p>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0 order-1 md:order-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MAIN 2-COLUMN LAYOUT ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- LEFT COLUMN (8 cols) --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- =====================================================================
                         SECTION D: REKOMENDASI AGRIFLOW AI (Selective Dark Navy Highlight)
                    ===================================================================== --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-6 sm:p-8 text-white shadow-xl border border-slate-800 space-y-5">
                        <div class="pointer-events-none absolute -top-20 -right-20 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-20 -left-16 w-56 h-56 rounded-full bg-emerald-500/15 blur-3xl"></div>

                        <div class="relative z-10 space-y-5">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-300">Keputusan Analisis Sebelumnya</p>
                                    <h2 class="text-xl sm:text-2xl font-black text-white">Rekomendasi AgriFlow AI</h2>
                                </div>
                            </div>

                            {{-- 1. Rekomendasi Utama --}}
                            <div class="rounded-2xl bg-gradient-to-r from-emerald-500/20 to-teal-500/10 border border-emerald-400/30 p-5 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/25 text-emerald-300 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-300">Rekomendasi Utama</p>
                                    <p class="text-base sm:text-lg font-black text-white mt-1 leading-snug">
                                        {{ $primaryRecommendation }}
                                    </p>
                                </div>
                            </div>

                            {{-- 2 & 3. Alasan Penilaian & Kesimpulan --}}
                            @if($explanationIndo !== '' || $showConclusion)
                                <div class="grid grid-cols-1 {{ ($explanationIndo !== '' && $showConclusion) ? 'md:grid-cols-2' : '' }} gap-4">
                                    @if($explanationIndo !== '')
                                        <div class="rounded-2xl bg-white/5 border border-white/10 p-5">
                                            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-indigo-300 mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                </svg>
                                                Alasan Penilaian
                                            </div>
                                            <p class="text-sm text-slate-200 leading-relaxed">{{ $explanationIndo }}</p>
                                        </div>
                                    @endif

                                    @if($showConclusion)
                                        <div class="rounded-2xl bg-white/5 border border-white/10 p-5">
                                            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-cyan-300 mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                Kesimpulan
                                            </div>
                                            <p class="text-sm text-slate-200 leading-relaxed">{{ $conclusionIndo }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- =====================================================================
                         SECTION B: PENILAIAN KONDISI PRODUK
                    ===================================================================== --}}
                    <div class="rounded-3xl bg-gradient-to-b from-white to-emerald-50/40 border border-emerald-200/80 p-6 sm:p-7 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-emerald-100 pb-5">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-700">Analisis Kesegaran & Penyimpanan</p>
                                    <h2 class="text-xl sm:text-[22px] font-black text-slate-900">Penilaian Kondisi Produk</h2>
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border text-sm font-extrabold w-fit {{ $conditionTheme['badge'] }}">
                                {{ $conditionLabel }}
                            </span>
                        </div>

                        {{-- Skor Perkiraan Kondisi Saat Tiba --}}
                        @if($predictionAvailable && $arrivalQuality !== null)
                            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-800">Perkiraan Kondisi Saat Tiba</p>
                                        <p class="text-sm text-slate-500 mt-0.5">Estimasi mutu produk pada saat analisis ini dilakukan.</p>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-black {{ $conditionTheme['score'] }}">{{ number_format((float) $arrivalQuality, 0) }}</span>
                                        <span class="text-base font-bold text-slate-400">/100</span>
                                    </div>
                                </div>
                                <div class="w-full h-3.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $conditionTheme['bar'] }}"
                                         style="width: {{ max(0, min(100, (float) $arrivalQuality)) }}%"></div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 font-semibold">
                                {{ $isStorageStability
                                    ? 'Komoditas ini menggunakan model stabilitas penyimpanan kering sehingga skor kesegaran produk segar tidak ditampilkan.'
                                    : 'Perkiraan kondisi saat tiba belum tersedia pada rekaman analisis ini.' }}
                            </div>
                        @endif

                        {{-- Kartu Suhu Produk & Kelembapan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Suhu Produk --}}
                            <div class="rounded-2xl p-5 bg-amber-50/80 border border-amber-200/90 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-amber-900">Suhu Produk</p>
                                        <p class="text-3xl font-black text-slate-900 mt-1.5">
                                            {{ $temperatureC !== null ? $fmtNum($temperatureC, 1) . '°C' : 'Belum dicatat' }}
                                        </p>
                                    </div>
                                    <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-amber-200/80 flex flex-wrap items-center justify-between gap-2 text-xs sm:text-[13px]">
                                    <span class="text-slate-700 font-medium">{{ $tempRef['full'] }}</span>
                                    @if($tempInRange === true)
                                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold">Sesuai Acuan</span>
                                    @elseif($tempInRange === false)
                                        <span class="px-2.5 py-0.5 rounded-md bg-amber-200 text-amber-950 font-bold">Di Luar Batas</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Kelembapan --}}
                            <div class="rounded-2xl p-5 bg-cyan-50/80 border border-cyan-200/90 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-cyan-900">Kelembapan</p>
                                        <p class="text-3xl font-black text-slate-900 mt-1.5">
                                            {{ $humidityPercent !== null ? $fmtNum($humidityPercent, 0) . '%' : 'Belum dicatat' }}
                                        </p>
                                    </div>
                                    <div class="w-11 h-11 rounded-xl bg-cyan-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-cyan-200/80 flex flex-wrap items-center justify-between gap-2 text-xs sm:text-[13px]">
                                    <span class="text-slate-700 font-medium">{{ $humRef['full'] }}</span>
                                    @if($humInRange === true)
                                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold">Sesuai Acuan</span>
                                    @elseif($humInRange === false)
                                        <span class="px-2.5 py-0.5 rounded-md bg-amber-200 text-amber-950 font-bold">Perlu Perhatian</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =====================================================================
                         SECTION C: HAL YANG PERLU DIPERHATIKAN
                    ===================================================================== --}}
                    <div class="rounded-3xl p-6 sm:p-7 border shadow-sm {{ empty($findings) ? 'bg-emerald-50/80 border-emerald-200' : 'bg-amber-50/80 border-amber-200' }}">
                        <div class="flex items-center gap-3.5 mb-5">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm {{ empty($findings) ? 'bg-emerald-600' : 'bg-amber-500' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl sm:text-[22px] font-black text-slate-900">Hal yang Perlu Diperhatikan</h2>
                                <p class="text-sm text-slate-600">Temuan utama yang terdeteksi saat analisis ini dibuat.</p>
                            </div>
                        </div>

                        @if(empty($findings))
                            <div class="rounded-2xl bg-white/90 border border-emerald-200 p-4 flex items-center gap-3.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                <p class="text-sm sm:text-base font-bold text-emerald-900">
                                    Tidak ada kendala utama yang terdeteksi pada rekaman analisis ini.
                                </p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($findings as $finding)
                                    @php $isRose = $finding['tone'] === 'rose'; @endphp
                                    <div class="rounded-2xl p-4 bg-white/95 border flex items-start gap-3.5 {{ $isRose ? 'border-rose-300' : 'border-amber-300/80' }}">
                                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $isRose ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                                        <div>
                                            <p class="text-sm sm:text-base font-extrabold {{ $isRose ? 'text-rose-900' : 'text-slate-900' }}">{{ $finding['title'] }}</p>
                                            <p class="text-sm mt-1 leading-relaxed text-slate-600">{{ $finding['desc'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- =====================================================================
                         SECTION F: DETAIL DATA & ANALISIS (Tertutup Secara Default)
                    ===================================================================== --}}
                    <details class="group rounded-3xl bg-gradient-to-br from-indigo-50/90 via-purple-50/50 to-white border border-indigo-200/90 shadow-sm overflow-hidden">
                        <summary class="cursor-pointer list-none p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-indigo-50/80 transition-colors">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg sm:text-xl font-black text-slate-900">Detail Data & Analisis</h2>
                                    <p class="text-sm text-slate-600">Bukti teknis, sumber data historis, dan referensi standar komoditas.</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-indigo-200 text-indigo-700 text-xs sm:text-sm font-bold shadow-sm w-fit">
                                <span class="group-open:hidden">Buka Detail</span>
                                <span class="hidden group-open:inline">Tutup Detail</span>
                                <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </summary>

                        <div class="p-6 pt-3 border-t border-indigo-100 space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 bg-white/95 rounded-2xl p-5 border border-indigo-100">
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Kualitas Saat Berangkat</p>
                                    <p class="text-base font-extrabold text-slate-900 mt-1">
                                        {{ $departureQuality !== null ? number_format((float) $departureQuality, 0) . '/100' : '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Penurunan Kualitas</p>
                                    <p class="text-base font-extrabold text-rose-600 mt-1">
                                        -{{ number_format((float) ($decisionAnalysis['quality_loss_during_transit'] ?? 0), 0) }} poin
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Waktu Aman Pengiriman</p>
                                    <p class="text-base font-extrabold text-slate-900 mt-1">
                                        {{ $safeTransitWindow !== null ? $fmtNum($safeTransitWindow, 1) . ' jam' : '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Usia Panen Saat Analisis</p>
                                    <p class="text-base font-extrabold text-slate-900 mt-1">
                                        {{ isset($qualityPrediction['harvest_age_days']) ? $fmtNum($qualityPrediction['harvest_age_days'], 1) . ' hari' : '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Sumber Data</p>
                                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $sourceIndo }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Waktu Pencatatan</p>
                                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $formatDateIndo($shipment->condition_recorded_at, true) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-500">Skor Tingkat Risiko</p>
                                    <p class="text-sm font-extrabold text-slate-800 mt-1">
                                        {{ $riskScore !== null ? number_format((float) $riskScore, 0) . '/100' : '—' }}
                                    </p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs font-bold text-slate-500">Referensi Ilmiah Komoditas</p>
                                    @if(!empty($commodityProfile['source_url']))
                                        <a href="{{ $commodityProfile['source_url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-sm font-bold text-indigo-700 hover:underline mt-1">
                                            {{ $commodityProfile['source_name'] ?? 'Lihat Referensi' }} ↗
                                        </a>
                                    @else
                                        <p class="text-sm font-bold text-slate-500 mt-1">Tidak tersedia</p>
                                    @endif
                                </div>
                            </div>

                            @if(count($recommendationsIndo) > 1)
                                <div class="rounded-2xl bg-white/95 border border-indigo-100 p-5">
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 mb-2">Seluruh Rekomendasi Tersimpan</p>
                                    <ul class="space-y-1.5 list-disc list-inside text-sm text-slate-700 font-medium">
                                        @foreach($recommendationsIndo as $recItem)
                                            <li>{{ $recItem }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </details>

                </div>

                {{-- RIGHT COLUMN (4 cols) --}}
                <div class="lg:col-span-4 space-y-6">

                    {{-- =====================================================================
                         SECTION E: AKSI CEPAT
                    ===================================================================== --}}
                    <div class="rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-900 p-6 text-white shadow-xl border border-slate-800 space-y-3.5">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-300">Aksi Cepat</p>

                        <button onclick="window.print()"
                                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-sm font-black transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                            </svg>
                            Cetak Ringkasan Analisis
                        </button>

                        <a href="{{ route('ai-analysis.history') }}"
                           class="w-full py-3.5 px-4 rounded-2xl bg-white/10 hover:bg-white/15 text-white text-sm font-bold transition-all border border-white/15 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Lihat Riwayat Analisis
                        </a>
                    </div>

                    {{-- =====================================================================
                         DATA SAAT ANALISIS (Soft Blue-White Card)
                    ===================================================================== --}}
                    <div class="rounded-3xl bg-gradient-to-b from-white to-sky-50/70 border border-sky-200/80 p-6 shadow-sm space-y-4">
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2.5">
                            <span class="w-2.5 h-6 rounded-full bg-cyan-500"></span>
                            Data Saat Analisis
                        </h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                                <span class="text-sm font-semibold text-slate-600">Status Pengiriman</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $statusIndo }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200/70">
                                <span class="text-sm font-semibold text-slate-600">Berat Panen</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ number_format($shipment->harvest?->weight ?? 0, 0, ',', '.') }} kg</span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200/70">
                                <span class="text-sm font-semibold text-slate-600">Sisa Masa Simpan</span>
                                <span class="text-sm font-extrabold text-blue-900">
                                    {{ $remainingShelfLife !== null ? $fmtNum($remainingShelfLife, 1) . ' hari' : '—' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-rose-50/80 border border-rose-200/70">
                                <span class="text-sm font-semibold text-slate-600">Batas Kesegaran</span>
                                <span class="text-sm font-extrabold text-rose-700">{{ $formatDateIndo($shipment->harvest?->expiry_date) }}</span>
                            </div>

                            @if($readinessScore !== null)
                                <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-200/70">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="font-semibold text-slate-700">Kesiapan Operasional</span>
                                        <span class="font-black text-indigo-800">{{ number_format((float) $readinessScore, 0) }}/100</span>
                                    </div>
                                    <div class="w-full h-2.5 rounded-full bg-indigo-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-500"
                                             style="width: {{ max(0, min(100, (float) $readinessScore)) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="p-4 rounded-2xl bg-purple-50/80 border border-purple-200/70">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="font-semibold text-slate-700">Data Pendukung</span>
                                    <span class="font-black text-purple-800">{{ number_format((float) $dataConfidence, 0) }}%</span>
                                </div>
                                <div class="w-full h-2.5 rounded-full bg-purple-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-indigo-500"
                                         style="width: {{ max(0, min(100, (float) $dataConfidence)) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>