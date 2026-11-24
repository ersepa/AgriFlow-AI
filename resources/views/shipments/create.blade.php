<x-app-layout>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 8px 28px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen bg-[#F8FAFC] bg-grid-pattern pb-16 animate-card">
        <div class="max-w-7xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= HEADER & NAVIGASI KEMBALI ================= --}}
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('shipments.index') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-extrabold text-slate-700 hover:text-emerald-700 hover:border-emerald-300 shadow-2xs transition-all group">
                        <svg class="w-4 h-4 text-emerald-600 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Kembali ke Daftar Pengiriman</span>
                    </a>

                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Formulir Pengiriman Baru</span>
                    </span>
                </div>

                <div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Buat Pengiriman Baru</h1>
                    <p class="text-slate-600 mt-1 font-medium text-sm sm:text-base">
                        Hubungkan hasil panen Anda dengan tujuan distribusi agar AgriFlow dapat menghitung rute dan kondisi kesegaran secara otomatis.
                    </p>
                </div>
            </div>

            {{-- ================= GRID 2 KOLOM ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- KOLOM KIRI: FORMULIR (7 col) --}}
                <div class="lg:col-span-7">
                    <div class="agri-card p-6 sm:p-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600"></div>

                        <form id="createShipmentForm" action="{{ route('shipments.store') }}" method="POST" class="space-y-6">
                            @csrf

                            {{-- KELOMPOK 1: INFORMASI HASIL PANEN (Soft Emerald) --}}
                            <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200/90 p-5 space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-slate-900">1. Informasi Hasil Panen</h2>
                                        <p class="text-xs text-slate-600">Pilih hasil panen yang akan dikirim. Asal dan berat terisi otomatis.</p>
                                    </div>
                                </div>

                                {{-- Pilih Komoditas Panen --}}
                                <div>
                                    <label for="harvestSelect" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                        Pilih Hasil Panen <span class="text-rose-600">*</span>
                                    </label>
                                    <select id="harvestSelect"
                                            name="harvest_id"
                                            required
                                            class="w-full rounded-xl bg-white border-emerald-300 text-sm font-bold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 py-3">
                                        @forelse($harvests as $harvest)
                                            @php
                                                $conditionProfile = $conditionProfiles[$harvest->id] ?? [];
                                            @endphp
                                            <option value="{{ $harvest->id }}"
                                                    @selected((string) old('harvest_id') === (string) $harvest->id)
                                                    data-origin="{{ $harvest->location }}"
                                                    data-weight="{{ $harvest->weight }}"
                                                    data-condition-model="{{ $conditionProfile['quality_model_type'] ?? 'shelf_life_quality' }}"
                                                    data-temp-min="{{ $conditionProfile['optimal_temp_min'] ?? '' }}"
                                                    data-temp-max="{{ $conditionProfile['optimal_temp_max'] ?? '' }}"
                                                    data-rh-min="{{ $conditionProfile['optimal_humidity_min'] ?? '' }}"
                                                    data-rh-max="{{ $conditionProfile['optimal_humidity_max'] ?? '' }}"
                                                    data-moisture-max="{{ $conditionProfile['safe_moisture_short_term_max_percent'] ?? '' }}"
                                                    data-dry-rh-max="{{ $conditionProfile['safe_relative_humidity_max_percent'] ?? '' }}"
                                                    data-source="{{ $conditionProfile['source_name'] ?? '' }}">
                                                {{ $harvest->commodity }} — {{ $harvest->location }} ({{ number_format((float) $harvest->weight, 0, ',', '.') }} kg)
                                            </option>
                                        @empty
                                            <option value="" disabled selected>Belum ada data hasil panen</option>
                                        @endforelse
                                    </select>
                                    @error('harvest_id')
                                        <p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Lokasi Asal & Berat Muatan (Autofill Readonly) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="originInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Lokasi Asal (Otomatis)
                                        </label>
                                        <input type="text"
                                               id="originInput"
                                               name="origin"
                                               readonly
                                               class="w-full rounded-xl bg-white/70 border-emerald-200 text-sm font-bold text-slate-700 cursor-not-allowed py-2.5">
                                        @error('origin')
                                            <p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="weightInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Berat Muatan (Otomatis)
                                        </label>
                                        <input type="text"
                                               id="weightInput"
                                               name="weight"
                                               readonly
                                               class="w-full rounded-xl bg-white/70 border-emerald-200 text-sm font-bold text-slate-700 cursor-not-allowed py-2.5">
                                        @error('weight')
                                            <p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- KELOMPOK 2: INFORMASI PERJALANAN (Soft Blue) --}}
                            <div class="rounded-2xl bg-blue-50/70 border border-blue-200/90 p-5 space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-slate-900">2. Informasi Perjalanan</h2>
                                        <p class="text-xs text-slate-600">Tentukan lokasi tujuan dan tahap awal pengiriman.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Lokasi Tujuan --}}
                                    <div>
                                        <label for="destinationInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Lokasi Tujuan / Gudang <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="text"
                                               id="destinationInput"
                                               name="destination"
                                               value="{{ old('destination') }}"
                                               placeholder="Contoh: Pasar Induk Kramat Jati, Jakarta"
                                               required
                                               class="w-full rounded-xl bg-white border-blue-300 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                        <p class="text-[11px] text-slate-500 mt-1">Gunakan nama kota atau pasar agar rute akurat.</p>
                                        @error('destination')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Status Awal Pengiriman --}}
                                    <div>
                                        <label for="statusInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Status Awal Pengiriman <span class="text-rose-600">*</span>
                                        </label>
                                        <select id="statusInput"
                                                name="status"
                                                required
                                                class="w-full rounded-xl bg-white border-blue-300 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                            <option value="Harvested" @selected(old('status') === 'Harvested')>Baru Dipanen</option>
                                            <option value="Packed" @selected(old('status') === 'Packed')>Siap Kirim (Dikemas)</option>
                                            <option value="In Transit" @selected(old('status') === 'In Transit')>Dalam Perjalanan</option>
                                        </select>
                                        <p class="text-[11px] text-slate-500 mt-1">Status dapat diperbarui kapan saja nanti.</p>
                                        @error('status')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- KELOMPOK 3: INFORMASI KONDISI PENGIRIMAN (Soft Cyan) --}}
                            <div class="rounded-2xl bg-cyan-50/70 border border-cyan-200/90 p-5 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v10.54a4 4 0 11-4 0V4a2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="text-base font-black text-slate-900">3. Kondisi Pengiriman <span class="text-xs font-bold text-cyan-700">(Opsional)</span></h2>
                                            <p class="text-xs text-slate-600">Kosongkan jika belum diukur. Sistem tidak akan mengarang data yang kosong.</p>
                                        </div>
                                    </div>

                                    <span id="conditionModelBadge" class="shrink-0 rounded-full border border-cyan-200 bg-white px-3 py-1 text-[11px] font-extrabold text-cyan-800 shadow-2xs">
                                        Produk Segar
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div id="temperatureConditionField">
                                        <label for="recorded_temperature_c" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Suhu Produk (°C)
                                        </label>
                                        <input type="number"
                                               step="0.1"
                                               min="-50"
                                               max="80"
                                               id="recorded_temperature_c"
                                               name="recorded_temperature_c"
                                               value="{{ old('recorded_temperature_c') }}"
                                               placeholder="Contoh: 12"
                                               class="w-full rounded-xl border-cyan-300 bg-white text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-cyan-500 focus:ring-cyan-500 py-2.5">
                                        @error('recorded_temperature_c')
                                            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div id="moistureConditionField" class="hidden">
                                        <label for="recorded_moisture_percent" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Kadar Air Produk (%)
                                        </label>
                                        <input type="number"
                                               step="0.1"
                                               min="0"
                                               max="100"
                                               id="recorded_moisture_percent"
                                               name="recorded_moisture_percent"
                                               value="{{ old('recorded_moisture_percent') }}"
                                               placeholder="Contoh: 10.5"
                                               class="w-full rounded-xl border-cyan-300 bg-white text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-cyan-500 focus:ring-cyan-500 py-2.5">
                                        @error('recorded_moisture_percent')
                                            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="recorded_relative_humidity_percent" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Kelembapan (%)
                                        </label>
                                        <input type="number"
                                               step="0.1"
                                               min="0"
                                               max="100"
                                               id="recorded_relative_humidity_percent"
                                               name="recorded_relative_humidity_percent"
                                               value="{{ old('recorded_relative_humidity_percent') }}"
                                               placeholder="Contoh: 85"
                                               class="w-full rounded-xl border-cyan-300 bg-white text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-cyan-500 focus:ring-cyan-500 py-2.5">
                                        @error('recorded_relative_humidity_percent')
                                            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div id="conditionReferenceHint" class="rounded-xl border border-cyan-200/80 bg-white/90 px-4 py-3 text-xs leading-relaxed text-slate-600 font-medium">
                                    Pilih hasil panen untuk melihat acuan kondisi penyimpanan komoditas.
                                </div>
                            </div>

                            {{-- TOMBOL AKSI --}}
                            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                                <a href="{{ route('shipments.index') }}"
                                   class="w-full sm:w-auto text-center px-6 py-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-sm transition-colors">
                                    Batal
                                </a>
                                <button type="submit"
                                        id="submitShipmentBtn"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm shadow-lg shadow-emerald-600/20 hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                                    <svg id="submitSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span id="submitBtnText">Buat Pengiriman</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: PANEL PANDUAN (5 col) --}}
                <div class="lg:col-span-5 space-y-5">

                    {{-- Panduan Analisis Otomatis --}}
                    <div class="agri-card p-6 bg-indigo-50/60 border border-indigo-200/80 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-base text-slate-900">Analisis Otomatis AgriFlow AI</h3>
                                <p class="text-xs text-indigo-700 font-bold">Apa yang terjadi setelah Anda menyimpan?</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs sm:text-sm text-slate-700 font-medium">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Data Panen Terhubung:</strong> Lokasi asal dan berat muatan diambil otomatis dari catatan panen agar data tetap akurat.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Perhitungan Rute & Waktu:</strong> Sistem menghitung jarak, estimasi waktu tempuh, dan perkiraan emisi perjalanan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Penilaian Kesegaran:</strong> Kondisi produk saat tiba langsung dinilai sesuai standar komoditas.</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Tips Praktis Pengiriman --}}
                    <div class="agri-card p-6 bg-amber-50/60 border border-amber-200/80 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16Z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-base text-slate-900">Tips Sebelum Berangkat</h3>
                                <p class="text-xs text-amber-800 font-bold">Panduan praktis menjaga mutu hasil panen</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs text-slate-700">
                            <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200/70">
                                <p class="font-extrabold text-slate-900">Gunakan Kemasan Berventilasi</p>
                                <p class="text-slate-600 mt-0.5">Sirkulasi udara yang baik membantu mencegah panas berlebih pada buah dan sayuran.</p>
                            </div>

                            <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200/70">
                                <p class="font-extrabold text-slate-900">Catat Suhu Awal Bila Tersedia</p>
                                <p class="text-slate-600 mt-0.5">Memasukkan suhu atau kelembapan awal membuat rekomendasi AI menjadi jauh lebih tepat.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Script Autofill, Referensi Komoditas & Loading State --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const harvestSelect = document.getElementById('harvestSelect');
            const originInput = document.getElementById('originInput');
            const weightInput = document.getElementById('weightInput');
            const temperatureField = document.getElementById('temperatureConditionField');
            const moistureField = document.getElementById('moistureConditionField');
            const temperatureInput = document.getElementById('recorded_temperature_c');
            const moistureInput = document.getElementById('recorded_moisture_percent');
            const conditionModelBadge = document.getElementById('conditionModelBadge');
            const conditionReferenceHint = document.getElementById('conditionReferenceHint');

            const form = document.getElementById('createShipmentForm');
            const submitBtn = document.getElementById('submitShipmentBtn');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitBtnText = document.getElementById('submitBtnText');

            function updateHarvestDetails() {
                if (harvestSelect && harvestSelect.options.length > 0 && harvestSelect.selectedIndex >= 0) {
                    const selectedOption = harvestSelect.options[harvestSelect.selectedIndex];
                    if (!selectedOption || !selectedOption.value) return;

                    originInput.value = selectedOption.dataset.origin || '';

                    const weightVal = selectedOption.dataset.weight;
                    weightInput.value = weightVal ? `${parseFloat(weightVal).toLocaleString('id-ID')} kg` : '';

                    const isDry = selectedOption.dataset.conditionModel === 'storage_stability';
                    temperatureField?.classList.toggle('hidden', isDry);
                    moistureField?.classList.toggle('hidden', !isDry);

                    if (temperatureInput) temperatureInput.disabled = isDry;
                    if (moistureInput) moistureInput.disabled = !isDry;

                    if (conditionModelBadge) {
                        conditionModelBadge.textContent = isDry ? 'Komoditas Kering' : 'Produk Segar';
                    }

                    if (conditionReferenceHint) {
                        const source = selectedOption.dataset.source
                            ? ` Sumber acuan: ${selectedOption.dataset.source}.`
                            : '';

                        if (isDry) {
                            const moisture = selectedOption.dataset.moistureMax;
                            const rh = selectedOption.dataset.dryRhMax;
                            const details = [
                                moisture ? `Kadar air ≤ ${moisture}%` : null,
                                rh ? `Kelembapan ≤ ${rh}%` : null,
                            ].filter(Boolean);

                            conditionReferenceHint.textContent = details.length
                                ? `Acuan penyimpanan komoditas kering: ${details.join(' · ')}.${source}`
                                : `Komoditas kering terpilih, namun batas kadar air/kelembapan spesifik belum tersedia.${source}`;
                        } else {
                            const tempMin = selectedOption.dataset.tempMin;
                            const tempMax = selectedOption.dataset.tempMax;
                            const rhMin = selectedOption.dataset.rhMin;
                            const rhMax = selectedOption.dataset.rhMax;

                            let tempText = null;
                            if (tempMin !== '' && tempMax !== '') {
                                tempText = parseFloat(tempMin) === parseFloat(tempMax)
                                    ? `Suhu acuan ${tempMin}°C`
                                    : `Suhu ${tempMin}–${tempMax}°C`;
                            }

                            let rhText = null;
                            if (rhMin !== '' && rhMax !== '') {
                                rhText = parseFloat(rhMin) === parseFloat(rhMax)
                                    ? `Kelembapan ${rhMin}%`
                                    : `Kelembapan ${rhMin}–${rhMax}%`;
                            }

                            const details = [tempText, rhText].filter(Boolean);

                            conditionReferenceHint.textContent = details.length
                                ? `Acuan kondisi komoditas: ${details.join(' · ')}.${source}`
                                : `Acuan suhu/kelembapan spesifik belum tersedia untuk komoditas ini.${source}`;
                        }
                    }
                }
            }

            updateHarvestDetails();

            if (harvestSelect) {
                harvestSelect.addEventListener('change', updateHarvestDetails);
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitSpinner?.classList.remove('hidden');
                    if (submitBtnText) submitBtnText.textContent = 'Menyimpan & Menganalisis...';
                });
            }
        });
    </script>
</x-app-layout>