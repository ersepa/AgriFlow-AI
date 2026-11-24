<x-app-layout>
    <script src="https://unpkg.com/lucide@latest"></script>

    @php
        $baselineAnalysis = data_get($baseline, 'analysis', []);
        $baselineRoute = data_get($baseline, 'route', []);
        $baselineCarbon = data_get($baseline, 'carbon', []);
        $isDryModel = data_get($baselineAnalysis, 'quality_prediction.condition_model_type') === 'storage_stability';

        $riskScore = data_get($baselineAnalysis, 'risk_score');
        $arrivalQuality = data_get($baselineAnalysis, 'quality_at_arrival');
        $storageStatus = data_get($baselineAnalysis, 'quality_prediction.storage_stability_assessment.status');
        $feasibilityRaw = data_get($baselineRoute, 'freshness_feasibility', 'Unavailable');
        $transitMargin = data_get($baselineRoute, 'transit_margin_hours');

        $riskMeta = match (true) {
            $riskScore === null => ['label' => 'Belum Tersedia', 'text' => 'text-slate-700', 'badge' => 'bg-slate-200/70 text-slate-700 border-slate-300', 'desc' => 'Data risiko belum tersedia.'],
            $riskScore >= 75 => ['label' => 'Sangat Tinggi', 'text' => 'text-rose-700', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200', 'desc' => 'Pengiriman ini memerlukan perhatian segera.'],
            $riskScore >= 45 => ['label' => 'Sedang', 'text' => 'text-amber-700', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200', 'desc' => 'Pantau kondisi untuk mencegah penurunan kualitas.'],
            default => ['label' => 'Risiko Rendah', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'desc' => 'Risiko pengiriman saat ini berada dalam batas aman.'],
        };

        $feasibilityMeta = match ($feasibilityRaw) {
            'Safe' => ['label' => 'Kondisi Pengiriman Sesuai', 'short' => 'Aman', 'color' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'card' => 'bg-emerald-50/75 border-emerald-200/80'],
            'Tight' => ['label' => 'Waktu Pengiriman Terbatas', 'short' => 'Ketat', 'color' => 'text-amber-700', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200', 'card' => 'bg-amber-50/75 border-amber-200/80'],
            'Breach' => ['label' => 'Waktu Pengiriman Melewati Batas Aman', 'short' => 'Melewati Batas', 'color' => 'text-rose-700', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200', 'card' => 'bg-rose-50/75 border-rose-200/80'],
            default => ['label' => 'Data Rute Belum Tersedia', 'short' => 'Belum Tersedia', 'color' => 'text-blue-700', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200', 'card' => 'bg-blue-50/75 border-blue-200/80'],
        };

        $defaultVehicle = data_get($baseline, 'input.vehicle', 'standard_truck');
        $defaultTemp = data_get($baseline, 'input.temperature_c') ?? data_get($shipment, 'temperature_c') ?? '';
        $defaultHumidity = data_get($baseline, 'input.relative_humidity_percent') ?? data_get($shipment, 'relative_humidity_percent') ?? '';
        $commodityTempMin = data_get($baseline, 'provenance.commodity_profile.optimal_temp_min_c');
        $commodityTempMax = data_get($baseline, 'provenance.commodity_profile.optimal_temp_max_c');

        $compareUrl = $shipment
            ? (\Illuminate\Support\Facades\Route::has('digital-twin.compare')
                ? route('digital-twin.compare', $shipment)
                : url("/digital-twin/{$shipment->id}/compare"))
            : '';

        $storeComparisonUrl = $shipment
            ? (\Illuminate\Support\Facades\Route::has('digital-twin.comparisons.store')
                ? route('digital-twin.comparisons.store', $shipment)
                : url("/digital-twin/{$shipment->id}/comparisons"))
            : '';

        $comparisonHistoryUrl = \Illuminate\Support\Facades\Route::has('digital-twin.comparisons.history')
            ? route('digital-twin.comparisons.history')
            : url('/digital-twin/comparisons/history');

        $scenarioHistoryUrl = \Illuminate\Support\Facades\Route::has('digital-twin.scenarios.history')
            ? route('digital-twin.scenarios.history')
            : url('/digital-twin/scenarios/history');
    @endphp

    <style>
        .agri-container {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 8px 24px -8px rgba(15, 23, 42, 0.05);
            border-radius: 1.25rem;
        }
        .tinted-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .tinted-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -8px rgba(15, 23, 42, 0.08);
        }
    </style>

    <div class="min-h-screen bg-slate-100/70 py-7 px-4 sm:px-6 lg:px-8 w-full">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- 1. MAIN HEADER --}}
            <section class="agri-container relative overflow-hidden p-6 sm:p-8 bg-gradient-to-br from-white via-indigo-50/30 to-teal-50/40">
                <div class="pointer-events-none absolute -top-20 -right-20 w-72 h-72 rounded-full bg-indigo-400/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 right-32 w-64 h-64 rounded-full bg-teal-400/10 blur-3xl"></div>
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 via-teal-500 to-emerald-500"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="space-y-3 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-sm font-semibold">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Simulator Skenario Logistik</span>
                        </div>

                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                                Digital Twin Operasional
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                            Uji berbagai kondisi pengiriman dan lihat pengaruhnya terhadap kualitas produk, waktu tempuh, dan risiko transportasi sebelum memilih rencana.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                        <a href="{{ $comparisonHistoryUrl }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold shadow-sm transition-colors">
                            <i data-lucide="chart-no-axes-combined" class="w-4 h-4 text-indigo-400"></i>
                            <span>Riwayat Perbandingan</span>
                        </a>
                    </div>
                </div>
            </section>

            {{-- Validation / Flash Alerts --}}
            @if($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold">Harap periksa kembali input simulasi Anda:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- 2. ACTIVE SHIPMENT SECTION (COLOR-CODED KPI CARDS) --}}
            <section class="agri-container p-5 sm:p-6 space-y-5">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="w-full md:max-w-lg">
                        <label for="shipmentSelector" class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-slate-700 mb-2">
                            <i data-lucide="package" class="w-4 h-4 text-blue-600"></i>
                            <span>Pilih Pengiriman Hasil Panen</span>
                        </label>
                        <form method="GET" action="{{ url()->current() }}">
                            <select id="shipmentSelector"
                                    name="shipment"
                                    onchange="this.form.submit()"
                                    class="w-full rounded-xl border-blue-200 bg-blue-50/50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-base font-semibold text-slate-800 py-3">
                                @forelse($shipments as $item)
                                    <option value="{{ $item->id }}" @selected($shipment && $shipment->id === $item->id)>
                                        #{{ $item->id }} — {{ ucfirst($item->harvest->commodity ?? 'Komoditas') }}
                                        ({{ $item->origin ?? 'Asal' }} → {{ $item->destination ?? 'Tujuan' }})
                                    </option>
                                @empty
                                    <option value="">Belum ada pengiriman aktif yang tersedia</option>
                                @endforelse
                            </select>
                        </form>
                    </div>

                    @if($shipment)
                        <div class="flex flex-wrap items-center gap-2.5 text-sm">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-50 border border-blue-200/80 font-semibold text-blue-800">
                                <i data-lucide="map-pin" class="w-4 h-4 text-blue-600"></i>
                                {{ $shipment->origin ?? 'Asal' }} → {{ $shipment->destination ?? 'Tujuan' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-indigo-50 border border-indigo-200/80 text-indigo-800 font-semibold">
                                Status: {{ match($shipment->status) { 'Harvested' => 'Baru Dipanen', 'Packed' => 'Siap Kirim', 'In Transit' => 'Dalam Perjalanan', 'Delivered' => 'Telah Tiba', default => $shipment->status ?? 'Aktif' } }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($shipment && $baseline)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-100">
                        {{-- Current Delivery Risk (Light Rose) --}}
                        <div class="tinted-card rounded-2xl bg-rose-50/75 border border-rose-200/80 p-5 flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-rose-900">Risiko Pengiriman Saat Ini</span>
                                <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2.5">
                                <span class="text-3xl font-bold {{ $riskMeta['text'] }}">
                                    {{ $riskScore !== null ? $riskScore : '—' }}<span class="text-sm font-medium text-rose-400">/100</span>
                                </span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $riskMeta['badge'] }}">
                                    {{ $riskMeta['label'] }}
                                </span>
                            </div>
                            <p class="text-sm text-rose-900/80 mt-2.5 leading-relaxed">{{ $riskMeta['desc'] }}</p>
                        </div>

                        {{-- Estimated Product Quality (Light Green) --}}
                        <div class="tinted-card rounded-2xl bg-emerald-50/75 border border-emerald-200/80 p-5 flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-emerald-900">
                                    {{ $isDryModel ? 'Kondisi Penyimpanan' : 'Perkiraan Kualitas Produk' }}
                                </span>
                                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i data-lucide="leaf" class="w-5 h-5"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                @if($isDryModel)
                                    <span class="text-2xl font-bold text-emerald-900">{{ $storageStatus ?? 'Belum tersedia' }}</span>
                                @else
                                    <span class="text-3xl font-bold text-emerald-900">
                                        {{ $arrivalQuality !== null ? $arrivalQuality : '—' }}<span class="text-sm font-medium text-emerald-600/70">/100</span>
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-emerald-900/80 mt-2.5 leading-relaxed">
                                Perkiraan kondisi hasil panen saat tiba dengan rencana saat ini.
                            </p>
                        </div>

                        {{-- Delivery Condition (Condition-tinted / Light Blue) --}}
                        <div class="tinted-card rounded-2xl border p-5 flex flex-col justify-between {{ $feasibilityMeta['card'] }}">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-800">Kondisi Pengiriman</span>
                                <div class="w-9 h-9 rounded-lg bg-white/80 {{ $feasibilityMeta['color'] }} flex items-center justify-center border border-slate-200/60">
                                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg border text-sm font-bold {{ $feasibilityMeta['badge'] }}">
                                    {{ $feasibilityMeta['label'] }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-700 mt-2.5">
                                Sisa waktu aman:
                                <strong class="text-slate-900">
                                    {{ $transitMargin !== null ? number_format($transitMargin, 1) . ' jam' : 'Belum tersedia' }}
                                </strong>
                            </p>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-8 text-center">
                        <i data-lucide="package-x" class="w-9 h-9 text-slate-400 mx-auto mb-2.5"></i>
                        <p class="text-base font-bold text-slate-800">Belum Ada Pengiriman Aktif yang Dipilih</p>
                        <p class="text-sm text-slate-500 mt-1">Buat pengiriman aktif terlebih dahulu untuk menjalankan simulasi Digital Twin.</p>
                    </div>
                @endif
            </section>

            @if($shipment && $baseline)
                {{-- 3. SIMULATION GUIDE & SCENARIO BUILDER --}}
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start"
                         x-data="digitalTwinApp({
                             compareUrl: @js($compareUrl),
                             csrfToken: @js(csrf_token()),
                             defaultVehicle: @js($defaultVehicle),
                             defaultTemp: @js($defaultTemp),
                             defaultHumidity: @js($defaultHumidity),
                             routeOptions: @js($routeOptions ?? [])
                         })">

                    {{-- Left Column: Redesigned Simulation Guide (4 cols) --}}
                    <div class="lg:col-span-4 space-y-4">
                        {{-- Light Indigo Intro Card + Compact Workflow --}}
                        <div class="tinted-card rounded-2xl bg-indigo-50/80 border border-indigo-200/80 p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <i data-lucide="info" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-indigo-950">Bagaimana Cara Kerja Simulasi Ini?</h2>
                                    <p class="text-sm text-indigo-900/80 mt-1 leading-relaxed">
                                        Uji hingga 3 skenario pengiriman secara berdampingan dengan rencana saat ini sebelum melakukan perubahan operasional.
                                    </p>
                                </div>
                            </div>

                            {{-- Compact 3-Step Workflow --}}
                            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-indigo-200/70 text-center">
                                <div class="rounded-xl bg-white/80 border border-indigo-100 p-2.5">
                                    <span class="text-xs font-bold text-indigo-600 block">1. Atur</span>
                                    <span class="text-xs font-semibold text-slate-700">Ubah input</span>
                                </div>
                                <div class="rounded-xl bg-white/80 border border-indigo-100 p-2.5">
                                    <span class="text-xs font-bold text-teal-600 block">2. Bandingkan</span>
                                    <span class="text-xs font-semibold text-slate-700">Lihat selisih</span>
                                </div>
                                <div class="rounded-xl bg-white/80 border border-indigo-100 p-2.5">
                                    <span class="text-xs font-bold text-emerald-600 block">3. Putuskan</span>
                                    <span class="text-xs font-semibold text-slate-700">Pilih rencana</span>
                                </div>
                            </div>
                        </div>

                        {{-- 4 Color-Coded Input Explanation Cards --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-600 px-1">Arti Setiap Input</h3>

                            {{-- Vehicle: Light Blue --}}
                            <div class="tinted-card rounded-xl bg-blue-50/75 border border-blue-200/80 p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="truck" class="w-4 h-4"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="font-bold text-blue-950">Kendaraan Pengiriman</p>
                                    <p class="text-blue-900/80 mt-0.5 leading-relaxed">
                                        Rencana transportasi & profil emisi. Pilihan kendaraan tidak menambah bonus kesegaran tersembunyi.
                                    </p>
                                </div>
                            </div>

                            {{-- Delay: Light Amber --}}
                            <div class="tinted-card rounded-xl bg-amber-50/75 border border-amber-200/80 p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="font-bold text-amber-950">Tambahan Waktu Tunda</p>
                                    <p class="text-amber-900/80 mt-0.5 leading-relaxed">
                                        Menguji perjalanan lebih lama (mis. 2 atau 5 jam). Penundaan mengurangi sisa waktu aman pengiriman.
                                    </p>
                                </div>
                            </div>

                            {{-- Temperature: Light Rose --}}
                            <div class="tinted-card rounded-xl bg-rose-50/75 border border-rose-200/80 p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="thermometer" class="w-4 h-4"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="font-bold text-rose-950">Suhu Penyimpanan (°C)</p>
                                    <p class="text-rose-900/80 mt-0.5 leading-relaxed">
                                        Suhu muatan selama perjalanan.
                                        @if($commodityTempMin !== null && $commodityTempMax !== null)
                                            Acuan: <strong class="text-rose-950">{{ $commodityTempMin }}°C–{{ $commodityTempMax }}°C</strong>.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Humidity: Light Cyan --}}
                            <div class="tinted-card rounded-xl bg-cyan-50/75 border border-cyan-200/80 p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="droplets" class="w-4 h-4"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="font-bold text-cyan-950">Kelembapan Penyimpanan (%)</p>
                                    <p class="text-cyan-900/80 mt-0.5 leading-relaxed">
                                        Tingkat kelembapan muatan untuk menguji pengaruhnya terhadap stabilitas produk.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Simulation Tips: Light Teal --}}
                        <div class="tinted-card rounded-2xl bg-teal-50/75 border border-teal-200/80 p-5">
                            <div class="flex items-center gap-2 text-teal-900 mb-2.5">
                                <i data-lucide="lightbulb" class="w-4 h-4 text-teal-600"></i>
                                <h3 class="text-sm font-bold uppercase tracking-wider">Tips Simulasi</h3>
                            </div>
                            <ul class="space-y-1.5 text-sm text-teal-900/85 list-disc list-inside leading-relaxed">
                                <li>Pertahankan Skenario A mendekati kondisi saat ini.</li>
                                <li>Ubah satu variabel sekaligus untuk melihat dampaknya.</li>
                                <li>Periksa "Apa yang Berubah?" meskipun skor utama sama.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Right Column: Scenario Builder + Visual Results (8 cols) --}}
                    <div class="lg:col-span-8 space-y-6">

                        {{-- SCENARIO BUILDER CONTAINER --}}
                        <div class="agri-container p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">Buat Skenario untuk Dibandingkan</h2>
                                    <p class="text-sm text-slate-600 mt-0.5">Setiap skenario memiliki warna identitas sendiri. Kolom yang diubah akan ditandai otomatis.</p>
                                </div>

                                <button type="button"
                                        x-show="scenarios.length < 3"
                                        @click="addScenario()"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 text-sm font-bold transition-colors">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Tambah Skenario</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(sc, idx) in scenarios" :key="idx">
                                    <div class="rounded-2xl border p-4 sm:p-5 space-y-4 transition-all"
                                         :class="scenarioTheme(idx).card">
                                        {{-- Scenario Card Header --}}
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2.5 flex-1 min-w-[200px]">
                                                <span class="w-9 h-9 rounded-xl text-white text-sm font-bold flex items-center justify-center shrink-0 shadow-sm"
                                                      :class="scenarioTheme(idx).badge"
                                                      x-text="String.fromCharCode(65 + idx)"></span>
                                                <input type="text"
                                                       x-model="sc.name"
                                                       placeholder="Nama skenario..."
                                                       class="w-full max-w-sm rounded-xl border-slate-200/90 bg-white/95 text-base font-bold text-slate-900 py-2 px-3.5 focus:border-indigo-500 focus:ring-indigo-500">
                                                <span x-show="hasAnyModification(sc)"
                                                      class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold">
                                                    Diubah
                                                </span>
                                            </div>

                                            <button type="button"
                                                    x-show="scenarios.length > 1"
                                                    @click="removeScenario(idx)"
                                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-600 hover:text-rose-700 px-3 py-1.5 rounded-lg hover:bg-rose-100/70 transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>

                                        {{-- Grouped Input Fields --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            {{-- Delivery Vehicle --}}
                                            <div class="rounded-xl bg-white/90 border border-slate-200/80 p-3">
                                                <div class="flex items-center justify-between gap-1 mb-2">
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                                        <i data-lucide="truck" class="w-4 h-4 text-blue-600"></i>
                                                        <span>Kendaraan</span>
                                                    </label>
                                                    <span x-show="isModified(sc, 'vehicle')"
                                                          class="text-[11px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Diubah</span>
                                                </div>
                                                <select x-model="sc.vehicle"
                                                        class="w-full rounded-lg border-slate-200 bg-slate-50/70 focus:bg-white text-sm font-semibold text-slate-800 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="standard_truck">Truk Standar</option>
                                                    <option value="refrigerated_truck">Truk Berpendingin</option>
                                                    <option value="electric_truck">Truk Listrik</option>
                                                </select>
                                            </div>

                                            {{-- Extra Delivery Delay --}}
                                            <div class="rounded-xl bg-white/90 border border-slate-200/80 p-3">
                                                <div class="flex items-center justify-between gap-1 mb-2">
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                                        <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                                                        <span>Tambahan Tunda</span>
                                                    </label>
                                                    <span x-show="isModified(sc, 'delay_hours')"
                                                          class="text-[11px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Diubah</span>
                                                </div>
                                                <div class="relative">
                                                    <input type="number"
                                                           step="0.5"
                                                           min="0"
                                                           max="168"
                                                           x-model.number="sc.delay_hours"
                                                           placeholder="0"
                                                           class="w-full rounded-lg border-slate-200 bg-slate-50/70 focus:bg-white text-sm font-semibold text-slate-800 py-2 pr-11 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">jam</span>
                                                </div>
                                            </div>

                                            {{-- Storage Temperature --}}
                                            <div class="rounded-xl bg-white/90 border border-slate-200/80 p-3">
                                                <div class="flex items-center justify-between gap-1 mb-2">
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                                        <i data-lucide="thermometer" class="w-4 h-4 text-rose-500"></i>
                                                        <span>Suhu</span>
                                                    </label>
                                                    <span x-show="isModified(sc, 'temperature_c')"
                                                          class="text-[11px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Diubah</span>
                                                </div>
                                                <div class="relative">
                                                    <input type="number"
                                                           step="0.5"
                                                           min="-20"
                                                           max="60"
                                                           x-model="sc.temperature_c"
                                                           placeholder="Saat ini"
                                                           class="w-full rounded-lg border-slate-200 bg-slate-50/70 focus:bg-white text-sm font-semibold text-slate-800 py-2 pr-9 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">°C</span>
                                                </div>
                                            </div>

                                            {{-- Storage Humidity --}}
                                            <div class="rounded-xl bg-white/90 border border-slate-200/80 p-3">
                                                <div class="flex items-center justify-between gap-1 mb-2">
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                                        <i data-lucide="droplets" class="w-4 h-4 text-cyan-600"></i>
                                                        <span>Kelembapan</span>
                                                    </label>
                                                    <span x-show="isModified(sc, 'relative_humidity_percent')"
                                                          class="text-[11px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Diubah</span>
                                                </div>
                                                <div class="relative">
                                                    <input type="number"
                                                           step="1"
                                                           min="0"
                                                           max="100"
                                                           x-model="sc.relative_humidity_percent"
                                                           placeholder="Saat ini"
                                                           class="w-full rounded-lg border-slate-200 bg-slate-50/70 focus:bg-white text-sm font-semibold text-slate-800 py-2 pr-8 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">%</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Compact Condition Summary --}}
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-700 pt-1">
                                            <i data-lucide="sliders-horizontal" class="w-4 h-4 shrink-0" :class="scenarioTheme(idx).icon"></i>
                                            <span x-text="scenarioSummary(sc)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-sm text-slate-600">
                                    Dievaluasi secara deterministik terhadap rencana pengiriman aktif Anda.
                                </p>

                                <button type="button"
                                        @click="runComparison()"
                                        :disabled="loading"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-bold shadow-sm transition-colors">
                                    <span x-show="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                                    <i x-show="!loading" data-lucide="play" class="w-4 h-4"></i>
                                    <span x-text="loading ? 'Menjalankan Simulasi...' : 'Bandingkan Skenario'"></span>
                                </button>
                            </div>

                            <div x-show="errorMessage"
                                 x-cloak
                                 class="mt-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 font-medium"
                                 x-text="errorMessage"></div>
                        </div>

                        {{-- 4. VISUAL SCENARIO COMPARISON RESULTS --}}
                        <div x-show="result" x-cloak class="space-y-5">

                            {{-- A. Strong Comparison Header + Decision Banner --}}
                            <div class="agri-container p-5 sm:p-6 space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="chart-no-axes-combined" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-slate-900">Hasil Perbandingan Skenario</h2>
                                            <p class="text-sm text-slate-600">Lihat bagaimana perbedaan kondisi pengiriman memengaruhi muatan Anda.</p>
                                        </div>
                                    </div>

                                    <span class="px-3.5 py-1.5 rounded-full text-sm font-bold border"
                                          :class="decisionBadgeClass()"
                                          x-text="decisionHeadline()"></span>
                                </div>

                                {{-- Decision Summary Strip --}}
                                <div class="rounded-xl border p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                     :class="decisionStripClass()">
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold uppercase tracking-wider opacity-80">Keputusan yang Disarankan</p>
                                        <p class="text-base font-semibold leading-relaxed"
                                           x-text="result?.comparison?.decision_reason ?? ''"></p>
                                    </div>
                                    <div class="shrink-0 sm:text-right">
                                        <p class="text-xs font-semibold opacity-75">Pilihan Utama</p>
                                        <p class="text-base font-bold" x-text="preferredOptionLabel()"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- B. Color-Coded Scenario Result Cards --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="(sc, idx) in (result?.comparison?.ranked_scenarios ?? [])" :key="idx">
                                    <div class="agri-container p-5 space-y-4 border-t-4"
                                         :class="scenarioTheme(idx).resultBorder">
                                        {{-- Card Top --}}
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-start gap-2.5">
                                                <span class="w-8 h-8 rounded-lg text-white text-sm font-bold flex items-center justify-center shrink-0 mt-0.5"
                                                      :class="scenarioTheme(idx).badge"
                                                      x-text="String.fromCharCode(65 + idx)"></span>
                                                <div>
                                                    <h3 class="text-lg font-bold text-slate-900"
                                                        x-text="sc.name ?? ('Skenario ' + String.fromCharCode(65 + idx))"></h3>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border mt-1"
                                                          :class="scenarioStatusClass(sc.baseline_decision?.classification)"
                                                          x-text="sc.baseline_decision?.status ?? 'Dievaluasi'"></span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-bold shrink-0"
                                                  x-text="'Peringkat #' + (idx + 1)"></span>
                                        </div>

                                        {{-- 4 Tinted Metric Cards --}}
                                        <div class="grid grid-cols-2 gap-3">
                                            {{-- Delivery Risk: Light Rose --}}
                                            <div class="rounded-xl bg-rose-50/80 border border-rose-200/80 p-3.5 space-y-2">
                                                <div class="flex items-center justify-between text-rose-800">
                                                    <span class="text-xs font-bold">Risiko Pengiriman</span>
                                                    <i data-lucide="shield-alert" class="w-4 h-4 text-rose-600"></i>
                                                </div>
                                                <div class="flex items-baseline justify-between gap-1">
                                                    <p class="text-xl font-bold text-rose-950">
                                                        <span x-text="sc.analysis?.risk_score ?? '—'"></span><span class="text-xs text-rose-500">/100</span>
                                                    </p>
                                                    <span class="text-xs font-bold"
                                                          :class="deltaColor(sc.baseline_decision?.delta?.risk_points, true)"
                                                          x-text="formatDelta(sc.baseline_decision?.delta?.risk_points, ' poin')"></span>
                                                </div>
                                                <div class="h-2 w-full rounded-full bg-rose-200/70 overflow-hidden">
                                                    <div class="h-full bg-rose-500 rounded-full"
                                                         :style="'width:' + Math.min(100, Math.max(0, Number(sc.analysis?.risk_score ?? 0))) + '%'"></div>
                                                </div>
                                            </div>

                                            {{-- Estimated Product Quality: Light Green --}}
                                            <div class="rounded-xl bg-emerald-50/80 border border-emerald-200/80 p-3.5 space-y-2">
                                                <div class="flex items-center justify-between text-emerald-800">
                                                    <span class="text-xs font-bold">Est. Kualitas</span>
                                                    <i data-lucide="leaf" class="w-4 h-4 text-emerald-600"></i>
                                                </div>
                                                <div class="flex items-baseline justify-between gap-1">
                                                    <p class="text-xl font-bold text-emerald-950"
                                                       x-text="sc.analysis?.quality_at_arrival !== null && sc.analysis?.quality_at_arrival !== undefined ? (sc.analysis.quality_at_arrival + '/100') : (sc.analysis?.quality_prediction?.storage_stability_assessment?.status ?? '—')"></p>
                                                    <span class="text-xs font-bold"
                                                          :class="deltaColor(sc.baseline_decision?.delta?.quality_points, false)"
                                                          x-text="formatDelta(sc.baseline_decision?.delta?.quality_points, ' poin')"></span>
                                                </div>
                                                <div x-show="sc.analysis?.quality_at_arrival !== null && sc.analysis?.quality_at_arrival !== undefined"
                                                     class="h-2 w-full rounded-full bg-emerald-200/70 overflow-hidden">
                                                    <div class="h-full bg-emerald-500 rounded-full"
                                                         :style="'width:' + Math.min(100, Math.max(0, Number(sc.analysis?.quality_at_arrival ?? 0))) + '%'"></div>
                                                </div>
                                            </div>

                                            {{-- Delivery Condition: Light Blue --}}
                                            <div class="rounded-xl bg-blue-50/80 border border-blue-200/80 p-3.5 space-y-1.5">
                                                <div class="flex items-center justify-between text-blue-800">
                                                    <span class="text-xs font-bold">Kondisi</span>
                                                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                                                </div>
                                                <p class="text-sm font-bold"
                                                   :class="feasibilityTextClass(sc.route?.freshness_feasibility)"
                                                   x-text="friendlyFeasibility(sc.route?.freshness_feasibility)"></p>
                                                <p class="text-xs font-semibold text-blue-900/80"
                                                   x-text="'Waktu aman: ' + (sc.route?.transit_margin_hours !== null && sc.route?.transit_margin_hours !== undefined ? Number(sc.route.transit_margin_hours).toFixed(1) + ' jam' : 'Tidak tersedia')"></p>
                                            </div>

                                            {{-- Carbon Emissions: Light Teal --}}
                                            <div class="rounded-xl bg-teal-50/80 border border-teal-200/80 p-3.5 space-y-1.5">
                                                <div class="flex items-center justify-between text-teal-800">
                                                    <span class="text-xs font-bold">Est. Karbon</span>
                                                    <i data-lucide="cloud" class="w-4 h-4 text-teal-600"></i>
                                                </div>
                                                <p class="text-xl font-bold text-teal-950"
                                                   x-text="sc.carbon?.estimated_kg !== null && sc.carbon?.estimated_kg !== undefined ? (sc.carbon.estimated_kg + ' kg') : '—'"></p>
                                                <p class="text-xs font-bold"
                                                   :class="deltaColor(sc.baseline_decision?.delta?.carbon_kg, true)"
                                                   x-text="formatDelta(sc.baseline_decision?.delta?.carbon_kg, ' kg')"></p>
                                            </div>
                                        </div>

                                        <p class="text-sm text-slate-600 leading-relaxed pt-2.5 border-t border-slate-100"
                                           x-text="sc.baseline_decision?.reason ?? ''"></p>
                                    </div>
                                </template>
                            </div>

                            {{-- C. "What Changed?" Section --}}
                            <div class="tinted-card rounded-2xl bg-amber-50/70 border border-amber-200/80 p-5 space-y-3">
                                <div class="flex items-center gap-2.5 text-amber-950">
                                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                                        <i data-lucide="git-compare" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold">Apa yang Berubah Antar Skenario?</h3>
                                        <p class="text-sm text-amber-900/80">Perbandingan langsung input dan selisih hasil perhitungan aktual.</p>
                                    </div>
                                </div>
                                <ul class="space-y-2 text-sm text-amber-950/90 list-disc list-inside pt-1 leading-relaxed">
                                    <template x-for="(line, i) in whatChangedLines()" :key="i">
                                        <li x-text="line"></li>
                                    </template>
                                </ul>
                            </div>

                            {{-- D. Prominent Final Decision Panel ("What Does This Result Mean?") --}}
                            <div class="tinted-card rounded-2xl border p-5 sm:p-6 space-y-3"
                                 :class="finalDecisionPanelClass()">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                                         :class="finalDecisionIconClass()">
                                        <i data-lucide="lightbulb" class="w-5 h-5"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-xs font-bold uppercase tracking-wider opacity-75">Apa Arti Hasil Ini?</p>
                                        <h3 class="text-lg sm:text-xl font-bold" x-text="finalDecisionTitle()"></h3>
                                        <p class="text-sm sm:text-base leading-relaxed opacity-90" x-text="plainLanguageSummary()"></p>
                                        <p class="text-sm font-semibold pt-2 border-t border-current/15" x-text="'Langkah Berikutnya: ' + recommendedNextAction()"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- E. Redesigned Save Comparison Panel (Light Teal) --}}
                            @if($storeComparisonUrl)
                                <form method="POST"
                                      action="{{ $storeComparisonUrl }}"
                                      class="tinted-card rounded-2xl bg-teal-50/80 border border-teal-200/90 p-5 space-y-3.5">
                                    @csrf
                                    <input type="hidden" name="scenarios_json" :value="JSON.stringify(cleanPayload())">

                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0">
                                            <i data-lucide="save" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <label for="comparisonNameInput" class="block text-base font-bold text-teal-950">
                                                Beri Nama Perbandingan Anda
                                            </label>
                                            <p class="text-sm text-teal-900/80 mt-0.5">
                                                Simpan rekaman perbandingan ini untuk ditinjau nanti atau dibagikan kepada tim operasional Anda.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1">
                                        <input id="comparisonNameInput"
                                               type="text"
                                               name="name"
                                               required
                                               placeholder="Contoh: Uji Pendingin vs Tunda 2 Jam"
                                               class="flex-1 rounded-xl border-teal-200 bg-white text-base py-2.5 px-4 focus:border-teal-500 focus:ring-teal-500">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-sm transition-colors shrink-0">
                                            <i data-lucide="bookmark-check" class="w-4 h-4"></i>
                                            <span>Simpan Perbandingan</span>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

        </div>
    </div>

    <script>
        function digitalTwinApp(config) {
            return {
                loading: false,
                errorMessage: '',
                result: null,
                defaults: {
                    vehicle: config.defaultVehicle || 'standard_truck',
                    delay_hours: 0,
                    temperature_c: config.defaultTemp === null ? '' : String(config.defaultTemp),
                    relative_humidity_percent: config.defaultHumidity === null ? '' : String(config.defaultHumidity)
                },
                scenarios: [
                    { name: 'Skenario A — Kondisi Saat Ini', vehicle: config.defaultVehicle || 'standard_truck', delay_hours: 0, temperature_c: config.defaultTemp, relative_humidity_percent: config.defaultHumidity },
                    { name: 'Skenario B — Pengiriman Tertunda', vehicle: config.defaultVehicle || 'standard_truck', delay_hours: 2, temperature_c: config.defaultTemp, relative_humidity_percent: config.defaultHumidity }
                ],

                scenarioTheme(idx) {
                    const themes = [
                        {
                            card: 'bg-indigo-50/55 border-indigo-200/80',
                            badge: 'bg-indigo-600',
                            icon: 'text-indigo-600',
                            resultBorder: 'border-t-indigo-500'
                        },
                        {
                            card: 'bg-teal-50/55 border-teal-200/80',
                            badge: 'bg-teal-600',
                            icon: 'text-teal-600',
                            resultBorder: 'border-t-teal-500'
                        },
                        {
                            card: 'bg-orange-50/55 border-orange-200/80',
                            badge: 'bg-orange-500',
                            icon: 'text-orange-600',
                            resultBorder: 'border-t-orange-500'
                        }
                    ];
                    return themes[idx % themes.length];
                },

                isModified(sc, field) {
                    const val = sc[field] === null || sc[field] === undefined ? '' : String(sc[field]);
                    const def = String(this.defaults[field] ?? '');
                    if (field === 'delay_hours') {
                        return Number(val || 0) !== Number(def || 0);
                    }
                    return val !== def;
                },

                hasAnyModification(sc) {
                    return ['vehicle', 'delay_hours', 'temperature_c', 'relative_humidity_percent']
                        .some(f => this.isModified(sc, f));
                },

                addScenario() {
                    if (this.scenarios.length >= 3) return;
                    const letter = String.fromCharCode(65 + this.scenarios.length);
                    this.scenarios.push({
                        name: `Skenario ${letter} — Kondisi Disesuaikan`,
                        vehicle: 'refrigerated_truck',
                        delay_hours: 0,
                        temperature_c: config.defaultTemp,
                        relative_humidity_percent: config.defaultHumidity
                    });
                    this.$nextTick(() => window.lucide?.createIcons());
                },

                removeScenario(index) {
                    if (this.scenarios.length <= 1) return;
                    this.scenarios.splice(index, 1);
                },

                scenarioSummary(sc) {
                    const vehicleLabel = {
                        standard_truck: 'Truk Standar',
                        refrigerated_truck: 'Truk Berpendingin',
                        electric_truck: 'Truk Listrik'
                    }[sc.vehicle] || sc.vehicle;
                    const delayText = Number(sc.delay_hours) > 0 ? `+${sc.delay_hours} jam tunda` : 'Tanpa tunda';
                    const tempText = sc.temperature_c !== '' && sc.temperature_c !== null ? `${sc.temperature_c}°C` : 'Suhu saat ini';
                    const humText = sc.relative_humidity_percent !== '' && sc.relative_humidity_percent !== null ? `${sc.relative_humidity_percent}% RH` : 'Kelembapan saat ini';
                    return `${vehicleLabel} • ${delayText} • ${tempText} • ${humText}`;
                },

                cleanPayload() {
                    return this.scenarios.map(sc => ({
                        name: sc.name || 'Skenario',
                        vehicle: sc.vehicle || 'standard_truck',
                        delay_hours: sc.delay_hours === '' || sc.delay_hours === null ? 0 : Number(sc.delay_hours),
                        temperature_c: sc.temperature_c === '' || sc.temperature_c === null ? null : Number(sc.temperature_c),
                        relative_humidity_percent: sc.relative_humidity_percent === '' || sc.relative_humidity_percent === null ? null : Number(sc.relative_humidity_percent)
                    }));
                },

                async runComparison() {
                    this.loading = true;
                    this.errorMessage = '';
                    try {
                        const res = await fetch(config.compareUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify({ scenarios: this.cleanPayload() })
                        });

                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data?.message || 'Gagal menjalankan perbandingan skenario.');
                        }
                        this.result = data;
                        this.$nextTick(() => window.lucide?.createIcons());
                    } catch (e) {
                        this.errorMessage = e.message || 'Terjadi kesalahan yang tidak terduga.';
                    } finally {
                        this.loading = false;
                    }
                },

                formatDelta(val, unit = '') {
                    if (val === null || val === undefined || Number.isNaN(Number(val))) return '—';
                    const num = Number(val);
                    if (Math.abs(num) < 0.01) return 'Tidak berubah';
                    return `${num > 0 ? '+' : ''}${num}${unit}`;
                },

                deltaColor(val, lowerIsBetter = true) {
                    if (val === null || val === undefined || Math.abs(Number(val)) < 0.01) return 'text-slate-500';
                    const isGood = lowerIsBetter ? Number(val) < 0 : Number(val) > 0;
                    return isGood ? 'text-emerald-700' : 'text-rose-700';
                },

                whatChangedLines() {
                    const ranked = this.result?.comparison?.ranked_scenarios ?? [];
                    if (ranked.length === 0) return [];

                    const lines = [];
                    ranked.forEach((sc, idx) => {
                        const name = sc.name || `Skenario ${String.fromCharCode(65 + idx)}`;
                        const d = sc.baseline_decision?.delta || {};
                        const changes = [];

                        if (typeof d.risk_points === 'number' && Math.abs(d.risk_points) >= 0.1) {
                            changes.push(`risiko pengiriman ${d.risk_points > 0 ? 'meningkat' : 'menurun'} sebesar ${Math.abs(d.risk_points)} poin`);
                        } else {
                            changes.push('risiko pengiriman tidak berubah');
                        }

                        if (typeof d.quality_points === 'number' && Math.abs(d.quality_points) >= 0.1) {
                            changes.push(`perkiraan kualitas ${d.quality_points > 0 ? 'meningkat' : 'menurun'} sebesar ${Math.abs(d.quality_points)} poin`);
                        } else if (d.quality_points !== null && d.quality_points !== undefined) {
                            changes.push('perkiraan kualitas tidak berubah');
                        }

                        if (typeof d.transit_margin_hours === 'number' && Math.abs(d.transit_margin_hours) >= 0.1) {
                            changes.push(`waktu aman pengiriman ${d.transit_margin_hours > 0 ? 'bertambah' : 'berkurang'} sebesar ${Math.abs(d.transit_margin_hours)} jam`);
                        }

                        if (typeof d.carbon_kg === 'number' && Math.abs(d.carbon_kg) >= 0.05) {
                            changes.push(`emisi karbon ${d.carbon_kg > 0 ? 'meningkat' : 'menurun'} sebesar ${Math.abs(d.carbon_kg)} kg`);
                        }

                        lines.push(`${name} vs Rencana Saat Ini: ${changes.join(', ')}.`);
                    });
                    return lines;
                },

                friendlyFeasibility(raw) {
                    if (raw === 'Safe') return 'Kondisi Sesuai';
                    if (raw === 'Tight') return 'Waktu Aman Terbatas';
                    if (raw === 'Breach') return 'Melewati Batas Aman';
                    return 'Belum Tersedia';
                },

                feasibilityTextClass(raw) {
                    if (raw === 'Safe') return 'text-emerald-700';
                    if (raw === 'Tight') return 'text-amber-700';
                    if (raw === 'Breach') return 'text-rose-700';
                    return 'text-slate-600';
                },

                scenarioStatusClass(cls) {
                    if (cls === 'recommended_over_current') return 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    if (cls === 'not_viable') return 'bg-rose-100 text-rose-800 border-rose-200';
                    if (cls === 'conditional') return 'bg-amber-100 text-amber-800 border-amber-200';
                    return 'bg-slate-100 text-slate-700 border-slate-200';
                },

                decisionHeadline() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') return 'Opsi Direkomendasikan';
                    if (type === 'not_viable') return 'Tidak Ada Opsi Aman';
                    return this.result?.comparison?.decision_status || 'Pertahankan Rencana Saat Ini';
                },

                decisionBadgeClass() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') return 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    if (type === 'not_viable') return 'bg-rose-100 text-rose-800 border-rose-200';
                    return 'bg-indigo-100 text-indigo-800 border-indigo-200';
                },

                decisionStripClass() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') return 'bg-emerald-50/80 border-emerald-200 text-emerald-950';
                    if (type === 'not_viable') return 'bg-rose-50/80 border-rose-200 text-rose-950';
                    return 'bg-indigo-50/80 border-indigo-200 text-indigo-950';
                },

                finalDecisionPanelClass() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') return 'bg-teal-50/80 border-teal-200 text-teal-950';
                    if (type === 'not_viable') return 'bg-rose-50/80 border-rose-200 text-rose-950';
                    return 'bg-indigo-50/80 border-indigo-200 text-indigo-950';
                },

                finalDecisionIconClass() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') return 'bg-teal-600 text-white';
                    if (type === 'not_viable') return 'bg-rose-600 text-white';
                    return 'bg-indigo-600 text-white';
                },

                finalDecisionTitle() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') {
                        return `Beralih ke ${this.result?.comparison?.recommended_scenario?.name || 'Skenario Direkomendasikan'}`;
                    }
                    if (type === 'not_viable') return 'Tinjau Kondisi Pengiriman Segera';
                    return 'Pertahankan Rencana Saat Ini';
                },

                recommendedNextAction() {
                    const type = this.result?.comparison?.decision_type;
                    if (type === 'recommended_over_current') {
                        return 'Simpan perbandingan ini dan terapkan pengaturan transportasi atau penyimpanan yang disarankan.';
                    }
                    if (type === 'not_viable') {
                        return 'Tidak ada skenario yang memenuhi batas aman—tinjau ulang jadwal keberangkatan, pendinginan, atau penanganan.';
                    }
                    return 'Lanjutkan dengan rencana pengiriman saat ini kecuali ada kendala operasional lain yang mengharuskan perubahan.';
                },

                preferredOptionLabel() {
                    const comp = this.result?.comparison;
                    if (!comp) return '—';
                    return comp.preferred_option === 'scenario'
                        ? (comp.recommended_scenario?.name || 'Skenario Direkomendasikan')
                        : 'Pertahankan Rencana Saat Ini';
                },

                plainLanguageSummary() {
                    const comp = this.result?.comparison;
                    if (!comp) return '';
                    const best = comp.best_available_scenario;
                    const delta = best?.baseline_decision?.delta || {};

                    if (comp.decision_type === 'recommended_over_current' && best) {
                        const parts = [`${best.name} memberikan hasil lebih baik dibandingkan rencana pengiriman saat ini.`];
                        if (typeof delta.risk_points === 'number' && delta.risk_points < 0) {
                            parts.push(`Risiko pengiriman berkurang sebesar ${Math.abs(delta.risk_points)} poin.`);
                        }
                        if (typeof delta.quality_points === 'number' && delta.quality_points > 0) {
                            parts.push(`Perkiraan kualitas produk meningkat sebesar ${delta.quality_points} poin.`);
                        }
                        if (typeof delta.transit_margin_hours === 'number' && delta.transit_margin_hours > 0) {
                            parts.push(`Waktu aman pengiriman bertambah sebesar ${delta.transit_margin_hours} jam.`);
                        }
                        return parts.join(' ');
                    }

                    if (comp.decision_type === 'not_viable') {
                        return 'Belum ada skenario yang memenuhi syarat kondisi pengiriman aman. Mengubah pengaturan ini saja belum cukup untuk menjaga produk dalam batas kesegaran aman.';
                    }

                    return 'Skenario alternatif belum memberikan peningkatan yang cukup signifikan untuk mengubah rencana pengiriman saat ini.';
                }
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</x-app-layout>