<x-app-layout>
    @php
        $qualityPrediction = $analysis['quality_prediction'] ?? [];
        $arrivalQuality = $analysis['quality_at_arrival'] ?? null;
        $departureQuality = $analysis['quality_at_departure'] ?? null;
        $remainingShelfLife = $analysis['predicted_remaining_shelf_life_days'] ?? null;
        $safeTransitWindow = $analysis['safe_transit_window_hours'] ?? null;
        $temperatureAssessment = $analysis['temperature_assessment'] ?? [];
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
        $temperatureC = $shipment->recorded_temperature_c ?? ($qualityPrediction['temperature_c'] ?? null);
        $humidityPercent = $shipment->recorded_relative_humidity_percent ?? null;
        $moisturePercent = $shipment->recorded_moisture_percent ?? null;
        $temperatureBasis = $qualityPrediction['temperature_basis'] ?? 'unknown';
        $dataConfidence = $analysis['data_confidence'] ?? 0;
        $commodityProfile = $analysis['commodity_profile'] ?? [];
        $isStorageStability =
            ($commodityProfile['quality_model_type'] ?? null) === 'storage_stability'
            || !empty($qualityPrediction['storage_stability_reference_available']);
        $predictionAvailable = $qualityPrediction['prediction_available'] ?? false;
        $expiryConstraintApplied = $qualityPrediction['expiry_constraint_applied'] ?? false;
        $reconciliationStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? null;
        $readinessScore = $analysis['operational_readiness_score'] ?? null;

        // Referensi suhu & kelembapan aktual dari profil komoditas
        $optTempMin = $commodityProfile['optimal_temp_min'] ?? null;
        $optTempMax = $commodityProfile['optimal_temp_max'] ?? null;
        $optHumMin = $commodityProfile['optimal_humidity_min'] ?? null;
        $optHumMax = $commodityProfile['optimal_humidity_max'] ?? null;

        // Helper format angka Indonesia (koma desimal, tanpa .0 berlebih)
        $fmtNum = function ($val, int $decimals = 1): string {
            $num = (float) $val;
            $formatted = number_format($num, $decimals, ',', '.');
            return $decimals > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
        };

        // Helper format acuan suhu & kelembapan (mencegah bug 7.5–7.5°C)
        $formatReference = function ($min, $max, string $unit, string $singlePrefix, string $rangePrefix) use ($fmtNum): array {
            $hasMin = is_numeric($min);
            $hasMax = is_numeric($max);

            if ($hasMin && $hasMax) {
                $minF = (float) $min;
                $maxF = (float) $max;
                if ($minF === $maxF) {
                    $val = $fmtNum($minF, 1) . $unit;
                    return ['valid' => true, 'short' => $val, 'full' => "{$singlePrefix}: {$val}"];
                }
                if ($minF < $maxF) {
                    $val = $fmtNum($minF, 1) . '–' . $fmtNum($maxF, 1) . $unit;
                    return ['valid' => true, 'short' => $val, 'full' => "{$rangePrefix}: {$val}"];
                }
                return ['valid' => false, 'short' => null, 'full' => 'Data acuan belum tersedia.'];
            }

            if ($hasMin || $hasMax) {
                $single = $hasMin ? (float) $min : (float) $max;
                $val = $fmtNum($single, 1) . $unit;
                return ['valid' => true, 'short' => $val, 'full' => "{$singlePrefix}: {$val}"];
            }

            return ['valid' => false, 'short' => null, 'full' => 'Data acuan belum tersedia.'];
        };

        $tempRef = $formatReference($optTempMin, $optTempMax, '°C', 'Suhu acuan', 'Kisaran suhu yang disarankan');
        $humRef = $formatReference($optHumMin, $optHumMax, '%', 'Kelembapan acuan', 'Kisaran kelembapan yang disarankan');

        // Evaluasi status kondisi produk
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

        // Cek apakah suhu & kelembapan berada di dalam batas acuan
        $tempInRange = null;
        if (is_numeric($temperatureC) && is_numeric($optTempMin) && is_numeric($optTempMax) && (float)$optTempMin <= (float)$optTempMax) {
            $tempInRange = ((float)$temperatureC >= (float)$optTempMin && (float)$temperatureC <= (float)$optTempMax);
        } elseif ($temperatureStatus === 'Optimal') {
            $tempInRange = true;
        } elseif (in_array($temperatureStatus, ['Chilling risk', 'Suboptimal', 'High risk', 'Outside reference'], true)) {
            $tempInRange = false;
        }

        $humInRange = null;
        if (is_numeric($humidityPercent) && is_numeric($optHumMin) && is_numeric($optHumMax) && (float)$optHumMin <= (float)$optHumMax) {
            $humInRange = ((float)$humidityPercent >= (float)$optHumMin && (float)$humidityPercent <= (float)$optHumMax);
        }

        // Translasi status rekonsiliasi masa simpan
        $reconciliationIndo = match ($reconciliationStatus) {
            'Recorded expiry is limiting' => 'Batas tanggal kedaluwarsa membatasi sisa masa simpan produk.',
            'Reference shelf life is limiting' => 'Batas masa simpan standar komoditas lebih ketat dari tanggal kedaluwarsa.',
            default => $reconciliationStatus ? 'Batas masa simpan perlu menjadi perhatian utama.' : 'Batas kedaluwarsa memengaruhi sisa waktu aman pengiriman.',
        };

        // Kumpulkan peringatan utama (padat & tanpa duplikasi)
        $alerts = [];

        if ($tempInRange === false || $temperatureStatus === 'Chilling risk') {
            $alerts[] = [
                'tone' => $temperatureStatus === 'Chilling risk' ? 'rose' : 'amber',
                'title' => $temperatureStatus === 'Chilling risk' ? 'Suhu Terlalu Rendah (Risiko Kerusakan Dingin)' : 'Suhu Di Luar Batas Acuan',
                'desc' => $tempRef['valid']
                    ? 'Suhu tercatat (' . $fmtNum($temperatureC, 1) . '°C) berada di luar ' . strtolower($tempRef['full']) . '. Suhu yang tidak sesuai dapat mempercepat penurunan kesegaran.'
                    : 'Suhu penyimpanan saat ini perlu disesuaikan agar kesegaran produk tetap terjaga.',
            ];
        }

        if ($humInRange === false) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Kelembapan Perlu Diperhatikan',
                'desc' => $humRef['valid']
                    ? 'Kelembapan tercatat (' . $fmtNum($humidityPercent, 0) . '%) berada di luar ' . strtolower($humRef['full']) . '.'
                    : 'Tingkat kelembapan saat ini perlu dipantau selama pengiriman.',
            ];
        }

        if ($expiryConstraintApplied) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Batas Waktu Penyimpanan Semakin Dekat',
                'desc' => $reconciliationIndo . ' Prioritaskan kelancaran pengiriman.',
            ];
        }

        if (is_numeric($arrivalQuality) && $arrivalQuality < 50) {
            $alerts[] = [
                'tone' => 'rose',
                'title' => 'Risiko Penurunan Kualitas Saat Tiba',
                'desc' => 'Perkiraan kondisi produk saat tiba tergolong rendah (' . number_format((float)$arrivalQuality, 0) . '/100). Pertimbangkan penyesuaian penanganan atau rute.',
            ];
        }

        // Tindakan Pengiriman (dinamis dari $routeDecision / $analysis, diterjemahkan dengan aman)
        $rawRouteAction = data_get($routeDecision, 'recommended_action')
            ?? data_get($routeDecision, 'route_action')
            ?? data_get($analysis, 'recommended_action')
            ?? 'Maintain current route and monitor freshness margin.';

        $translateAction = function (string $text): string {
            $trimmed = trim($text);
            $exactMap = [
                'Maintain current route and monitor freshness margin.' => 'Tetap gunakan rute saat ini dan pantau kesegaran produk selama perjalanan.',
                'Continue monitoring the shipment against its current operational constraints.' => 'Lanjutkan pemantauan pengiriman sesuai kondisi operasional saat ini.',
                'Review shipment' => 'Tinjau kembali rencana dan kondisi pengiriman ini.',
            ];
            if (isset($exactMap[$trimmed])) {
                return $exactMap[$trimmed];
            }
            if (stripos($trimmed, 'Maintain current route') !== false) {
                return 'Tetap gunakan rute saat ini dan pantau kesegaran produk selama perjalanan.';
            }
            return $trimmed;
        };

        $actionRecommendation = $translateAction((string) $rawRouteAction);

        // Status pengiriman
        $statusMap = match ($shipment->status) {
            'Harvested' => ['label' => 'Baru Dipanen', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'Packed' => ['label' => 'Siap Kirim (Dikemas)', 'class' => 'bg-cyan-100 text-cyan-800 border-cyan-300'],
            'In Transit' => ['label' => 'Dalam Perjalanan', 'class' => 'bg-indigo-100 text-indigo-800 border-indigo-300'],
            'Delivered' => ['label' => 'Selesai Dikirim', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            default => ['label' => $shipment->status, 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
        };

        // Format tanggal Indonesia
        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatDateIndo = function ($date, bool $withTime = false) use ($bulanIndo): string {
            if (!$date) return 'Belum diatur';
            $carbon = \Carbon\Carbon::parse($date);
            $base = $carbon->format('d') . ' ' . ($bulanIndo[(int)$carbon->format('n')] ?? $carbon->format('M')) . ' ' . $carbon->format('Y');
            return $withTime ? $base . ' · ' . $carbon->format('H:i') : $base;
        };

        $harvestDate = $shipment->harvest->harvest_date ?? $shipment->harvest->created_at;

        // Sumber data dalam Bahasa Indonesia
        $rawSource = $shipment->condition_source ?: $temperatureBasis;
        $sourceIndo = match ($rawSource) {
            'manual_entry' => 'Input Manual',
            'recorded' => 'Data Tercatat',
            'ambient_fallback', 'estimated' => 'Estimasi Lingkungan',
            default => ucwords(str_replace('_', ' ', (string) $rawSource)),
        };

        // Helper waktu relatif bahasa Indonesia (untuk Riwayat Analisis)
        $formatRelativeIndo = function ($date): string {
            if (!$date) return 'Baru saja';
            $text = \Carbon\Carbon::parse($date)->locale('id')->diffForHumans();
            return str_replace(' yang lalu', ' lalu', $text);
        };

        // Helper lokalisasi teks dinamis AI (lapisan tampilan, tanpa mengubah data tersimpan)
        $localizeAiText = function (?string $text): string {
            $clean = trim((string) $text);
            if ($clean === '') {
                return '';
            }

            // Pemetaan kalimat utuh yang umum dihasilkan DecisionEngine / ShipmentController
            $exactTranslations = [
                'Maintain current route and monitor freshness margin.' => 'Tetap gunakan rute saat ini dan pantau kesegaran produk selama perjalanan.',
                'Continue monitoring the shipment against its current operational constraints.' => 'Lanjutkan pemantauan pengiriman sesuai kondisi operasional saat ini.',
                'Preserve the current operational window and reassess when recorded conditions change.' => 'Pertahankan jadwal pengiriman saat ini dan lakukan evaluasi ulang jika kondisi penyimpanan berubah.',
                'Review shipment' => 'Tinjau kembali kondisi dan rencana pengiriman ini.',
                'Prioritize dispatch and monitor temperature closely.' => 'Utamakan jadwal keberangkatan dan pantau suhu penyimpanan secara ketat.',
                'Prioritize dispatch and re-evaluate within 24 hours.' => 'Utamakan pengiriman ini dan lakukan pemeriksaan ulang dalam 24 jam.',
                'Expedite delivery and verify cold-chain temperature control.' => 'Percepat pengiriman dan pastikan pengaturan suhu pendingin bekerja dengan baik.',
                'Verify recorded cargo temperature and relative humidity.' => 'Periksa kembali suhu dan kelembapan produk yang tercatat.',
                'Record cargo temperature and humidity to improve assessment confidence.' => 'Catat suhu dan kelembapan produk agar hasil penilaian lebih akurat.',
                'Keep the current shipment plan and continue standard monitoring.' => 'Pertahankan rencana pengiriman saat ini dan lanjutkan pemantauan rutin.',
            ];

            if (isset($exactTranslations[$clean])) {
                return $exactTranslations[$clean];
            }

            // Pola kalimat dinamis dengan angka/parameter
            $regexRules = [
                '/^Prioritize (?:dispatch|shipment|delivery)(?: and)?\s*(?:re-?check|re-?evaluate|review) within (\d+)\s*hours?\.?$/i' => 'Utamakan pengiriman ini dan lakukan pemeriksaan ulang dalam $1 jam.',
                '/^Maintain current route and monitor freshness margin\.?$/i' => 'Tetap gunakan rute saat ini dan pantau kesegaran produk selama perjalanan.',
                '/^Re-?evaluate (?:shipment|conditions?) within (\d+)\s*hours?\.?$/i' => 'Lakukan pemeriksaan ulang kondisi pengiriman dalam $1 jam.',
                '/^Adjust (?:storage|cargo|cooling) temperature toward ([\d\.\-–]+)\s*°?C\.?$/i' => 'Sesuaikan suhu penyimpanan mendekati $1°C.',
                '/^Keep (?:storage|cargo) temperature within ([\d\.\-–]+)\s*°?C\.?$/i' => 'Jaga suhu penyimpanan tetap berada di kisaran $1°C.',
                '/^Monitor (?:cargo|shipment) temperature and (?:relative )?humidity during transit\.?$/i' => 'Pantau suhu dan kelembapan produk selama proses pengiriman.',
            ];

            foreach ($regexRules as $pattern => $replacement) {
                if (preg_match($pattern, $clean)) {
                    return preg_replace($pattern, $replacement, $clean);
                }
            }

            // Lokalisasi klausa teknis yang sering muncul pada Explanation / Conclusion
            $phraseReplacements = [
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
                '/\bcritical operational risk\b/i' => 'risiko pengiriman tingkat kritis',
                '/\brecorded cargo temperature\b/i' => 'suhu produk tercatat',
                '/\brecorded relative humidity\b/i' => 'kelembapan tercatat',
                '/\brecorded expiry\b/i' => 'batas kedaluwarsa tercatat',
                '/\boutside reference range\b/i' => 'di luar batas acuan',
                '/\bwithin reference range\b/i' => 'dalam batas acuan',
                '/\bchilling risk\b/i' => 'risiko kerusakan dingin',
                '/\btransit duration\b/i' => 'durasi perjalanan',
                '/\bshipment\b/i' => 'pengiriman',
                '/\bContinue monitoring\b/i' => 'Lanjutkan pemantauan',
                '/\bPreserve the current operational window\b/i' => 'Pertahankan jadwal pengiriman saat ini',
                '/\band reassess when recorded conditions change\b/i' => 'dan evaluasi ulang saat kondisi berubah',
                '/\bagainst its current operational constraints\b/i' => 'sesuai batas operasional saat ini',
                '/\bwithin (\d+) hours?\b/i' => 'dalam $1 jam',
                '/\b(\d+(?:\.\d+)?) days?\b/i' => '$1 hari',
                '/\b(\d+(?:\.\d+)?) hours?\b/i' => '$1 jam',
            ];

            $translated = preg_replace(array_keys($phraseReplacements), array_values($phraseReplacements), $clean);
            return trim((string) $translated);
        };

        // Helper ringkas teks maksimal 2 kalimat pendek
        $summarizeShort = function (string $text, int $maxChars = 190): string {
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ($text === '') return '';
            $sentences = preg_split('/(?<=[.!?])\s+/', $text) ?: [$text];
            $short = implode(' ', array_slice($sentences, 0, 2));
            if (mb_strlen($short) > $maxChars) {
                return rtrim(mb_substr($short, 0, $maxChars - 1)) . '…';
            }
            return $short;
        };

        // Parser untuk tiap entri riwayat analisis
        $parseHistoryEntry = function ($historyAnalysis) use ($localizeAiText, $summarizeShort): array {
            $rawText = trim((string) ($historyAnalysis->recommendations ?? ''));
            $rawRecs = [];
            $rawExplanation = '';
            $rawConclusion = '';

            if ($rawText !== '') {
                if (str_contains($rawText, 'Recommendations:')) {
                    $afterRecs = trim(preg_replace('/^.*?Recommendations:\s*/s', '', $rawText, 1));
                    $expParts = preg_split('/\R*Explanation:\s*/', $afterRecs, 2) ?: [];
                    $recText = trim($expParts[0] ?? '');
                    $expAndConcl = trim($expParts[1] ?? '');

                    $conclParts = preg_split('/\R*Conclusion:\s*/', $expAndConcl, 2) ?: [];
                    $rawExplanation = trim($conclParts[0] ?? '');
                    $rawConclusion = trim($conclParts[1] ?? '');

                    $rawRecs = array_values(array_filter(array_map(
                        static fn (string $line): string => trim(preg_replace('/^\s*[-•]\s*/', '', $line)),
                        preg_split('/\R+/', $recText) ?: []
                    )));
                } elseif (str_contains($rawText, ' — ')) {
                    [$act, $rsn] = array_pad(explode(' — ', $rawText, 2), 2, '');
                    $rawRecs = [trim($act)];
                    $rawExplanation = trim($rsn);
                } else {
                    $rawRecs = [$rawText];
                }
            }

            $recsIndo = array_values(array_filter(array_map($localizeAiText, $rawRecs)));
            $explanationIndo = $localizeAiText($rawExplanation);
            $conclusionIndo = $localizeAiText($rawConclusion);

            $primaryRec = $recsIndo[0] ?? 'Pantau kondisi pengiriman secara berkala sesuai standar komoditas.';
            $shortExplanation = $summarizeShort($explanationIndo, 180);
            $shortConclusion = $summarizeShort($conclusionIndo, 160);

            // Hindari duplikasi antara Kesimpulan dengan Rekomendasi atau Alasan Penilaian
            $norm = fn (string $s): string => mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $s));
            $showConclusionInPreview = $shortConclusion !== ''
                && $norm($shortConclusion) !== $norm($primaryRec)
                && $norm($shortConclusion) !== $norm($shortExplanation);

            $hasMoreDetails = count($recsIndo) > 1
                || mb_strlen($explanationIndo) > mb_strlen($shortExplanation)
                || ($conclusionIndo !== '' && !$showConclusionInPreview)
                || mb_strlen($conclusionIndo) > mb_strlen($shortConclusion);

            return [
                'primary_rec' => $primaryRec,
                'all_recs' => $recsIndo,
                'short_explanation' => $shortExplanation,
                'full_explanation' => $explanationIndo,
                'short_conclusion' => $showConclusionInPreview ? $shortConclusion : '',
                'full_conclusion' => $conclusionIndo,
                'has_more_details' => $hasMoreDetails,
            ];
        };
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-sky-50/70 to-emerald-50/60 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- ================= TOP NAVIGATION (UNCHANGED) ================= --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-emerald-700 hover:border-emerald-300 shadow-sm transition-all duration-200 hover:-translate-x-0.5 text-sm font-bold">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Dashboard
                </a>

                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm font-extrabold shadow-sm {{ $statusMap['class'] }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-current"></span>
                    {{ $statusMap['label'] }}
                </span>
            </div>

            {{-- =====================================================================
                 SECTION A: RINGKASAN PENGIRIMAN (UNCHANGED)
            ===================================================================== --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100/80 via-emerald-50 to-teal-50/90 border border-emerald-200 p-6 sm:p-8 shadow-md shadow-emerald-900/5">
                <div class="pointer-events-none absolute -top-20 -right-20 w-64 h-64 rounded-full bg-teal-300/25 blur-3xl"></div>

                <div class="relative z-10 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/25 flex-shrink-0">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2 text-sm font-bold">
                                    <span class="text-emerald-800">Detail Pengiriman</span>
                                    <span class="text-emerald-400">•</span>
                                    <span class="text-slate-600">ID #{{ $shipment->id }}</span>
                                </div>
                                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 capitalize tracking-tight mt-0.5">
                                    {{ $shipment->harvest->commodity }}
                                </h1>
                            </div>
                        </div>

                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/90 border border-emerald-200 text-slate-800 text-sm font-extrabold shadow-sm w-fit">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            {{ number_format($shipment->harvest->weight ?? 0, 0, ',', '.') }} kg
                        </div>
                    </div>

                    {{-- Route Card --}}
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
                                <p class="text-xs font-semibold text-slate-500 mt-1.5">Jarak & Estimasi Perjalanan</p>
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
                         SECTION B: KONDISI PRODUK (UNCHANGED)
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
                                    <p class="text-xs font-bold text-emerald-700">Analisis Kesegaran</p>
                                    <h2 class="text-xl sm:text-[22px] font-black text-slate-900">Kondisi Produk</h2>
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
                                        <p class="text-sm text-slate-500 mt-0.5">Dihitung dari usia panen, durasi perjalanan, dan kondisi penyimpanan.</p>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-black {{ $conditionTheme['score'] }}">{{ number_format((float)$arrivalQuality, 0) }}</span>
                                        <span class="text-base font-bold text-slate-400">/100</span>
                                    </div>
                                </div>
                                <div class="w-full h-3.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $conditionTheme['bar'] }} transition-all duration-500"
                                         style="width: {{ max(0, min(100, (float)$arrivalQuality)) }}%"></div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 font-semibold">
                                {{ $isStorageStability
                                    ? 'Komoditas ini menggunakan pemantauan stabilitas penyimpanan kering.'
                                    : 'Perkiraan kondisi saat tiba belum tersedia untuk pengiriman ini.' }}
                            </div>
                        @endif

                        {{-- Kartu Suhu (Soft Amber) & Kelembapan (Soft Cyan) --}}
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
                                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold">Normal</span>
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
                                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold">Normal</span>
                                    @elseif($humInRange === false)
                                        <span class="px-2.5 py-0.5 rounded-md bg-amber-200 text-amber-950 font-bold">Perlu Perhatian</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =====================================================================
                         SECTION C: STATUS & PERINGATAN PENGIRIMAN (UNCHANGED)
                    ===================================================================== --}}
                    <div class="rounded-3xl p-6 sm:p-7 border shadow-sm {{ empty($alerts) ? 'bg-emerald-50/80 border-emerald-200' : 'bg-amber-50/80 border-amber-200' }}">
                        <div class="flex items-center gap-3.5 mb-5">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm {{ empty($alerts) ? 'bg-emerald-600' : 'bg-amber-500' }}">
                                @if(empty($alerts))
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-xl sm:text-[22px] font-black text-slate-900">Status & Peringatan Pengiriman</h2>
                                <p class="text-sm text-slate-600">Hal penting yang perlu Anda perhatikan saat ini.</p>
                            </div>
                        </div>

                        @if(empty($alerts))
                            <div class="rounded-2xl bg-white/90 border border-emerald-200 p-4 flex items-center gap-3.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                <p class="text-sm sm:text-base font-bold text-emerald-900">
                                    Tidak ada peringatan utama. Kondisi pengiriman berada dalam batas aman.
                                </p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($alerts as $alert)
                                    @php $isRose = $alert['tone'] === 'rose'; @endphp
                                    <div class="rounded-2xl p-4 bg-white/90 border flex items-start gap-3.5 {{ $isRose ? 'border-rose-300' : 'border-amber-300/80' }}">
                                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $isRose ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                                        <div>
                                            <p class="text-sm sm:text-base font-extrabold {{ $isRose ? 'text-rose-900' : 'text-slate-900' }}">{{ $alert['title'] }}</p>
                                            <p class="text-sm mt-1 leading-relaxed text-slate-600">{{ $alert['desc'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- =====================================================================
                         SECTION AI ACTION: TINDAKAN PENGIRIMAN (UNCHANGED)
                    ===================================================================== --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 p-6 sm:p-7 text-white shadow-lg border border-slate-800">
                        <div class="pointer-events-none absolute -top-16 -right-16 w-48 h-48 rounded-full bg-emerald-500/20 blur-3xl"></div>

                        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-300">Rekomendasi AI · Tindakan Pengiriman</p>
                                <p class="text-lg sm:text-xl font-black text-white mt-1 leading-snug">
                                    {{ $actionRecommendation }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- =====================================================================
                         REDESIGNED BOTTOM SECTION A: DETAIL DATA & ANALISIS (Expandable)
                    ===================================================================== --}}
                    <details open class="group rounded-3xl bg-gradient-to-br from-indigo-50/90 via-purple-50/50 to-sky-50/60 border border-indigo-200/90 shadow-md shadow-indigo-900/5 overflow-hidden">
                        <summary class="cursor-pointer list-none p-6 sm:p-7 bg-gradient-to-r from-indigo-600/10 via-purple-600/10 to-transparent hover:from-indigo-600/15 hover:via-purple-600/15 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-600/25">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 17V9"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 17V5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 17v-3"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl sm:text-[22px] font-black text-slate-900">Detail Data & Analisis</h2>
                                    <p class="text-sm text-slate-600 mt-0.5">Ringkasan data teknis, pembaruan kondisi produk, dan riwayat analisis pengiriman.</p>
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-indigo-200 text-indigo-700 text-sm font-bold shadow-sm flex-shrink-0 w-fit">
                                <span class="group-open:hidden">Buka Detail</span>
                                <span class="hidden group-open:inline">Sembunyikan</span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </summary>

                        <div class="p-6 sm:p-7 pt-5 border-t border-indigo-200/70 space-y-8">

                            {{-- 6 Compact Visual Cards for Technical Data --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {{-- 1. Kualitas Saat Berangkat (Emerald) --}}
                                <div class="rounded-2xl bg-emerald-50/90 border border-emerald-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-emerald-900 leading-tight">Kualitas Saat Berangkat</p>
                                    </div>
                                    <p class="text-2xl font-black text-slate-900">
                                        {{ $departureQuality !== null ? number_format((float)$departureQuality, 0) : '—' }}<span class="text-sm font-bold text-slate-500">/100</span>
                                    </p>
                                    <p class="text-xs text-slate-600 mt-1">Kondisi awal saat produk dikirim.</p>
                                </div>

                                {{-- 2. Penurunan Kualitas (Amber/Rose) --}}
                                <div class="rounded-2xl bg-amber-50/90 border border-amber-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 17l-8.5-8.5-5 5L2 7"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 17h6v-6"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-amber-950 leading-tight">Penurunan Kualitas</p>
                                    </div>
                                    <p class="text-2xl font-black text-rose-600">
                                        -{{ number_format((float)($analysis['quality_loss_during_transit'] ?? 0), 0) }} <span class="text-sm font-bold text-rose-500">poin</span>
                                    </p>
                                    <p class="text-xs text-slate-600 mt-1">Perkiraan penurunan selama perjalanan.</p>
                                </div>

                                {{-- 3. Waktu Aman Pengiriman (Cyan/Blue) --}}
                                <div class="rounded-2xl bg-cyan-50/90 border border-cyan-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-cyan-950 leading-tight">Waktu Aman Pengiriman</p>
                                    </div>
                                    <p class="text-2xl font-black text-slate-900">
                                        {{ $safeTransitWindow !== null ? $fmtNum($safeTransitWindow, 1) : '—' }} <span class="text-sm font-bold text-slate-500">jam</span>
                                    </p>
                                    <p class="text-xs text-slate-600 mt-1">Batas durasi aman sebelum kualitas turun.</p>
                                </div>

                                {{-- 4. Sumber Data (Indigo) --}}
                                <div class="rounded-2xl bg-indigo-50/90 border border-indigo-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <ellipse cx="12" cy="5" rx="9" ry="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 5v14a9 3 0 0 0 18 0V5"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 3 0 0 0 18 0"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-indigo-950 leading-tight">Sumber Data</p>
                                    </div>
                                    <p class="text-base font-black text-slate-900">{{ $sourceIndo }}</p>
                                    <p class="text-xs text-slate-600 mt-1">Asal pencatatan kondisi produk.</p>
                                </div>

                                {{-- 5. Waktu Pencatatan (Sky Blue) --}}
                                <div class="rounded-2xl bg-sky-50/90 border border-sky-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-sky-950 leading-tight">Waktu Pencatatan</p>
                                    </div>
                                    <p class="text-base font-black text-slate-900">{{ $formatDateIndo($shipment->condition_recorded_at, true) }}</p>
                                    <p class="text-xs text-slate-600 mt-1">Pembaruan data kondisi terakhir.</p>
                                </div>

                                {{-- 6. Referensi Komoditas (Purple) --}}
                                <div class="rounded-2xl bg-purple-50/90 border border-purple-200/80 p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-extrabold text-purple-950 leading-tight">Referensi Komoditas</p>
                                    </div>
                                    @if(!empty($commodityProfile['source_url']))
                                        <a href="{{ $commodityProfile['source_url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-sm font-black text-purple-700 hover:text-purple-900 hover:underline leading-snug">
                                            {{ $commodityProfile['source_name'] ?? 'Lihat Referensi' }}
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/>
                                            </svg>
                                        </a>
                                    @else
                                        <p class="text-sm font-bold text-slate-500">Data acuan belum tersedia</p>
                                    @endif
                                    <p class="text-xs text-slate-600 mt-1">Acuan standar pascapanen.</p>
                                </div>
                            </div>

                            {{-- =====================================================================
                                 REDESIGNED BOTTOM SECTION B: PERBARUI KONDISI PRODUK
                            ===================================================================== --}}
                            @if(Route::has('shipments.conditions.update'))
                                <form method="POST" action="{{ route('shipments.conditions.update', $shipment) }}"
                                      class="rounded-3xl bg-gradient-to-br from-emerald-50/90 via-white to-cyan-50/80 border border-emerald-200/90 p-6 shadow-sm space-y-5">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex items-start gap-3.5">
                                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-500/20">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Perbarui Kondisi Produk</h3>
                                            <p class="text-sm text-slate-600 mt-0.5">Masukkan hasil pengukuran terbaru agar AgriFlow dapat menilai kembali kondisi produk.</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        {{-- Input 1: Suhu Produk --}}
                                        <div class="rounded-2xl bg-amber-50/80 border border-amber-200/90 p-4 space-y-2">
                                            <label for="recorded_temperature_c" class="flex items-center gap-2 text-sm font-extrabold text-amber-950">
                                                <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                                    </svg>
                                                </span>
                                                Suhu Produk (°C)
                                            </label>
                                            <input id="recorded_temperature_c" type="number" step="0.1" name="recorded_temperature_c"
                                                   placeholder="Contoh: 8.5"
                                                   value="{{ old('recorded_temperature_c', $shipment->recorded_temperature_c) }}"
                                                   class="w-full rounded-xl bg-white border-amber-300 text-base font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-amber-500 focus:ring-amber-500">
                                            <p class="text-xs text-slate-600">Suhu ruang penyimpanan atau muatan.</p>
                                            @error('recorded_temperature_c')
                                                <p class="text-xs font-bold text-rose-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- Input 2: Kelembapan --}}
                                        <div class="rounded-2xl bg-cyan-50/80 border border-cyan-200/90 p-4 space-y-2">
                                            <label for="recorded_relative_humidity_percent" class="flex items-center gap-2 text-sm font-extrabold text-cyan-950">
                                                <span class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                                    </svg>
                                                </span>
                                                Kelembapan (%)
                                            </label>
                                            <input id="recorded_relative_humidity_percent" type="number" step="0.1" name="recorded_relative_humidity_percent"
                                                   placeholder="Contoh: 85"
                                                   value="{{ old('recorded_relative_humidity_percent', $shipment->recorded_relative_humidity_percent) }}"
                                                   class="w-full rounded-xl bg-white border-cyan-300 text-base font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-cyan-500 focus:ring-cyan-500">
                                            <p class="text-xs text-slate-600">Tingkat kelembapan udara di sekitar produk.</p>
                                            @error('recorded_relative_humidity_percent')
                                                <p class="text-xs font-bold text-rose-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- Input 3: Kadar Air (Opsional) --}}
                                        <div class="rounded-2xl bg-indigo-50/80 border border-indigo-200/90 p-4 space-y-2">
                                            <label for="recorded_moisture_percent" class="flex items-center gap-2 text-sm font-extrabold text-indigo-950">
                                                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                                    </svg>
                                                </span>
                                                Kadar Air (%) <span class="text-xs font-semibold text-indigo-600">Opsional</span>
                                            </label>
                                            <input id="recorded_moisture_percent" type="number" step="0.1" name="recorded_moisture_percent"
                                                   placeholder="Contoh: 14"
                                                   value="{{ old('recorded_moisture_percent', $moisturePercent) }}"
                                                   class="w-full rounded-xl bg-white border-indigo-300 text-base font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-indigo-500 focus:ring-indigo-500">
                                            <p class="text-xs text-slate-600">Utamanya untuk komoditas kering/biji-bijian.</p>
                                            @error('recorded_moisture_percent')
                                                <p class="text-xs font-bold text-rose-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                        <p class="text-xs text-slate-500">Data baru akan langsung memperbarui hasil penilaian pengiriman ini.</p>
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-black shadow-lg shadow-emerald-600/25 hover:-translate-y-0.5 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Simpan & Analisis Ulang
                                        </button>
                                    </div>
                                </form>
                            @endif

                            {{-- =====================================================================
                                 REDESIGNED BOTTOM SECTION C: RIWAYAT ANALISIS
                            ===================================================================== --}}
                            <div class="space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-cyan-300 flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Riwayat Analisis</h3>
                                            <p class="text-sm text-slate-600">Catatan hasil penilaian kondisi pengiriman sebelumnya.</p>
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-extrabold w-fit">
                                        {{ $shipment->aiAnalyses->count() }} catatan tersimpan
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    @forelse($shipment->aiAnalyses as $historyAnalysis)
                                        @php
                                            $parsed = $parseHistoryEntry($historyAnalysis);
                                            $riskKey = strtolower(trim((string) ($historyAnalysis->risk_level ?? 'low')));

                                            $riskStyle = match (true) {
                                                str_contains($riskKey, 'critical') => [
                                                    'label' => 'Risiko Kritis',
                                                    'card' => 'bg-rose-50/70 border-rose-200',
                                                    'badge' => 'bg-rose-600 text-white',
                                                    'rec_box' => 'bg-white border-rose-200',
                                                    'rec_icon' => 'bg-rose-100 text-rose-700',
                                                ],
                                                str_contains($riskKey, 'high') => [
                                                    'label' => 'Risiko Tinggi',
                                                    'card' => 'bg-orange-50/70 border-orange-200',
                                                    'badge' => 'bg-orange-500 text-white',
                                                    'rec_box' => 'bg-white border-orange-200',
                                                    'rec_icon' => 'bg-orange-100 text-orange-700',
                                                ],
                                                str_contains($riskKey, 'medium') || str_contains($riskKey, 'moderate') => [
                                                    'label' => 'Risiko Sedang',
                                                    'card' => 'bg-amber-50/70 border-amber-200',
                                                    'badge' => 'bg-amber-500 text-white',
                                                    'rec_box' => 'bg-white border-amber-200',
                                                    'rec_icon' => 'bg-amber-100 text-amber-800',
                                                ],
                                                default => [
                                                    'label' => 'Risiko Rendah',
                                                    'card' => 'bg-emerald-50/70 border-emerald-200',
                                                    'badge' => 'bg-emerald-600 text-white',
                                                    'rec_box' => 'bg-white border-emerald-200',
                                                    'rec_icon' => 'bg-emerald-100 text-emerald-700',
                                                ],
                                            };
                                        @endphp

                                        <div class="rounded-2xl border p-5 shadow-sm space-y-4 transition-all hover:shadow-md {{ $riskStyle['card'] }}">
                                            {{-- A. Card Header --}}
                                            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200/70">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold shadow-sm {{ $riskStyle['badge'] }}">
                                                    @if(str_contains($riskKey, 'low'))
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                                        </svg>
                                                    @endif
                                                    {{ $riskStyle['label'] }}
                                                </span>

                                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                    </svg>
                                                    {{ $formatRelativeIndo($historyAnalysis->created_at) }}
                                                </span>
                                            </div>

                                            {{-- B. Rekomendasi Utama --}}
                                            <div class="rounded-xl border p-4 flex items-start gap-3 {{ $riskStyle['rec_box'] }}">
                                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $riskStyle['rec_icon'] }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Rekomendasi</p>
                                                    <p class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5 leading-snug">
                                                        {{ $parsed['primary_rec'] }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- C & D. Alasan Penilaian + Kesimpulan (Ringkas, Tanpa Duplikasi) --}}
                                            @if($parsed['short_explanation'] !== '' || $parsed['short_conclusion'] !== '')
                                                <div class="grid grid-cols-1 {{ ($parsed['short_explanation'] !== '' && $parsed['short_conclusion'] !== '') ? 'md:grid-cols-2' : '' }} gap-3">
                                                    @if($parsed['short_explanation'] !== '')
                                                        <div class="rounded-xl bg-white/80 border border-slate-200/80 p-3.5">
                                                            <p class="text-xs font-extrabold text-indigo-700 mb-1">Alasan Penilaian</p>
                                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $parsed['short_explanation'] }}</p>
                                                        </div>
                                                    @endif

                                                    @if($parsed['short_conclusion'] !== '')
                                                        <div class="rounded-xl bg-white/80 border border-slate-200/80 p-3.5">
                                                            <p class="text-xs font-extrabold text-emerald-700 mb-1">Kesimpulan</p>
                                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $parsed['short_conclusion'] }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- E. Expandable Detail (Hidden by Default) --}}
                                            @if($parsed['has_more_details'] || !empty($parsed['all_recs']))
                                                <details class="group/item">
                                                    <summary class="cursor-pointer list-none inline-flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-indigo-700 hover:text-indigo-900 transition-colors">
                                                        <span class="group-open/item:hidden">Lihat Detail Analisis</span>
                                                        <span class="hidden group-open/item:inline">Tutup Detail Analisis</span>
                                                        <svg class="w-4 h-4 transition-transform group-open/item:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </summary>

                                                    <div class="mt-3 rounded-xl bg-white border border-slate-200 p-4 space-y-3 text-sm text-slate-700">
                                                        @if(!empty($parsed['all_recs']))
                                                            <div>
                                                                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Seluruh Rekomendasi</p>
                                                                <ul class="space-y-1.5 list-disc list-inside text-slate-800 font-medium">
                                                                    @foreach($parsed['all_recs'] as $recItem)
                                                                        <li>{{ $recItem }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif

                                                        @if($parsed['full_explanation'] !== '')
                                                            <div class="pt-2 border-t border-slate-100">
                                                                <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 mb-1">Alasan Penilaian Lengkap</p>
                                                                <p class="leading-relaxed">{{ $parsed['full_explanation'] }}</p>
                                                            </div>
                                                        @endif

                                                        @if($parsed['full_conclusion'] !== '')
                                                            <div class="pt-2 border-t border-slate-100">
                                                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 mb-1">Kesimpulan Lengkap</p>
                                                                <p class="leading-relaxed">{{ $parsed['full_conclusion'] }}</p>
                                                            </div>
                                                        @endif

                                                        <div class="pt-2 border-t border-slate-100 text-xs text-slate-400 font-semibold">
                                                            Dicatat pada: {{ $formatDateIndo($historyAnalysis->created_at, true) }}
                                                        </div>
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="rounded-2xl bg-white/80 border border-dashed border-indigo-200 p-8 text-center">
                                            <p class="text-sm font-bold text-slate-600">Belum ada riwayat analisis tersimpan untuk pengiriman ini.</p>
                                            <p class="text-xs text-slate-500 mt-1">Riwayat akan muncul otomatis setelah Anda memperbarui kondisi produk.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </details>

                </div>

                {{-- RIGHT COLUMN (4 cols - UNCHANGED) --}}
                <div class="lg:col-span-4 space-y-6">

                    {{-- =====================================================================
                         AKSI CEPAT (UNCHANGED)
                    ===================================================================== --}}
                    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-6 text-white shadow-xl border border-slate-800 space-y-3.5">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-300">Aksi Cepat</p>

                        <button onclick="window.print()"
                                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-sm font-black transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                            </svg>
                            Cetak Ringkasan Pengiriman
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
                         INFORMASI PENGIRIMAN (UNCHANGED)
                    ===================================================================== --}}
                    <div class="rounded-3xl bg-gradient-to-b from-white to-sky-50/60 border border-sky-200/80 p-6 shadow-sm space-y-4">
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2.5">
                            <span class="w-2.5 h-6 rounded-full bg-cyan-500"></span>
                            Informasi Pengiriman
                        </h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200/70">
                                <span class="text-sm font-semibold text-slate-600">Tanggal Panen</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $formatDateIndo($harvestDate) }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-rose-50/80 border border-rose-200/70">
                                <span class="text-sm font-semibold text-slate-600">Batas Kesegaran</span>
                                <span class="text-sm font-extrabold text-rose-700">{{ $formatDateIndo($shipment->harvest->expiry_date) }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200/70">
                                <span class="text-sm font-semibold text-slate-600">Sisa Masa Simpan</span>
                                <span class="text-sm font-extrabold text-blue-900">
                                    {{ $remainingShelfLife !== null ? $fmtNum($remainingShelfLife, 1) . ' hari' : '—' }}
                                </span>
                            </div>

                            @if($readinessScore !== null)
                                <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-200/70">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="font-semibold text-slate-700">Kesiapan Operasional</span>
                                        <span class="font-black text-indigo-800">{{ number_format((float)$readinessScore, 0) }}/100</span>
                                    </div>
                                    <div class="w-full h-2.5 rounded-full bg-indigo-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-500"
                                             style="width: {{ max(0, min(100, (float)$readinessScore)) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="p-4 rounded-2xl bg-purple-50/80 border border-purple-200/70">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="font-semibold text-slate-700">Data Pendukung</span>
                                    <span   class="font-black text-purple-800">{{ number_format((float)$dataConfidence, 0) }}%</span>
                                </div>
                                <div class="w-full h-2.5 rounded-full bg-purple-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-indigo-500"
                                         style="width: {{ max(0, min(100, (float)$dataConfidence)) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>