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
                    <a href="{{ route('harvests.index') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-extrabold text-slate-700 hover:text-emerald-700 hover:border-emerald-300 shadow-2xs transition-all group">
                        <svg class="w-4 h-4 text-emerald-600 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Kembali ke Daftar Hasil Panen</span>
                    </a>

                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Pencatatan Hasil Panen</span>
                    </span>
                </div>

                <div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Tambah Hasil Panen</h1>
                    <p class="text-slate-600 mt-1 font-medium text-sm sm:text-base">
                        Catat hasil panen lapangan secara akurat untuk mempermudah perencanaan pengiriman dan pemantauan kesegaran produk.
                    </p>
                </div>
            </div>

            {{-- ================= GRID 2 KOLOM ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- KOLOM KIRI: FORMULIR (7 col) --}}
                <div class="lg:col-span-7">
                    <div class="agri-card p-6 sm:p-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600"></div>

                        <form id="createHarvestForm" action="{{ route('harvests.store') }}" method="POST" class="space-y-6">
                            @csrf

                            {{-- KELOMPOK 1: INFORMASI KOMODITAS (Soft Emerald) --}}
                            <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200/90 p-5 space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-slate-900">1. Informasi Komoditas</h2>
                                        <p class="text-xs text-slate-600">Masukkan jenis komoditas, total berat panen, dan lokasi asal.</p>
                                    </div>
                                </div>

                                {{-- Nama Komoditas --}}
                                <div>
                                    <label for="commodityInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                        Nama Komoditas <span class="text-rose-600">*</span>
                                    </label>
                                    <input type="text"
                                           id="commodityInput"
                                           name="commodity"
                                           value="{{ old('commodity') }}"
                                           placeholder="Contoh: Cabai Merah, Tomat Segar, Kopi Arabika"
                                           required
                                           class="w-full rounded-xl bg-white border-emerald-300 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                                    <p class="text-[11px] text-slate-500 mt-1">Gunakan nama komoditas yang jelas agar sistem mengenali standar penyimpanannya.</p>
                                    @error('commodity')
                                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Berat Hasil Panen --}}
                                    <div>
                                        <label for="weightInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Berat Hasil Panen (kg) <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="number"
                                               id="weightInput"
                                               step="0.01"
                                               min="0.01"
                                               name="weight"
                                               value="{{ old('weight') }}"
                                               placeholder="Contoh: 500"
                                               required
                                               class="w-full rounded-xl bg-white border-emerald-300 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                                        @error('weight')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Lokasi Panen --}}
                                    <div>
                                        <label for="locationInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Lokasi Panen / Gudang <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="text"
                                               id="locationInput"
                                               name="location"
                                               value="{{ old('location') }}"
                                               placeholder="Contoh: Lembang, Bandung Barat"
                                               required
                                               class="w-full rounded-xl bg-white border-emerald-300 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                                        @error('location')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- KELOMPOK 2: INFORMASI WAKTU (Soft Blue) --}}
                            <div class="rounded-2xl bg-blue-50/70 border border-blue-200/90 p-5 space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-slate-900">2. Informasi Waktu</h2>
                                        <p class="text-xs text-slate-600">Tentukan tanggal panen dan perkiraan batas kesegaran produk.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Tanggal Panen --}}
                                    <div>
                                        <label for="harvestDateInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Tanggal Panen <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="date"
                                               id="harvestDateInput"
                                               name="harvest_date"
                                               value="{{ old('harvest_date') }}"
                                               required
                                               class="w-full rounded-xl bg-white border-blue-300 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                        <p class="text-[11px] text-slate-500 mt-1">Digunakan untuk menghitung usia panen produk.</p>
                                        @error('harvest_date')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Tanggal Kedaluwarsa --}}
                                    <div>
                                        <label for="expiryDateInput" class="block text-xs font-extrabold text-slate-700 mb-1.5">
                                            Perkiraan Tanggal Kedaluwarsa <span class="text-xs font-semibold text-blue-700">(Opsional)</span>
                                        </label>
                                        <input type="date"
                                               id="expiryDateInput"
                                               name="expiry_date"
                                               value="{{ old('expiry_date') }}"
                                               class="w-full rounded-xl bg-white border-blue-300 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                        <p class="text-[11px] text-slate-500 mt-1">Batas waktu operasional sebelum produk dikirim.</p>
                                        @error('expiry_date')
                                            <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TOMBOL AKSI --}}
                            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                                <a href="{{ route('harvests.index') }}"
                                   class="w-full sm:w-auto text-center px-6 py-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-sm transition-colors">
                                    Batal
                                </a>
                                <button type="submit"
                                        id="submitHarvestBtn"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm shadow-lg shadow-emerald-600/20 hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                                    <svg id="harvestSubmitSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span id="harvestSubmitText">Simpan Hasil Panen</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: PANEL PANDUAN (5 col) --}}
                <div class="lg:col-span-5 space-y-5">

                    {{-- Manfaat Data Panen --}}
                    <div class="agri-card p-6 bg-indigo-50/60 border border-indigo-200/80 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-base text-slate-900">Dasar Analisis Pascapanen</h3>
                                <p class="text-xs text-indigo-700 font-bold">Bagaimana data ini digunakan oleh AgriFlow AI</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs sm:text-sm text-slate-700 font-medium">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Berat Panen:</strong> Menjadi dasar volume muatan pengiriman dan perkiraan emisi angkutan darat (CO₂e).</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Batas Kesegaran:</strong> Tanggal kedaluwarsa membantu sistem menilai sisa masa simpan secara lebih akurat.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-600 font-black mt-0.5">✓</span>
                                <span><strong class="text-slate-900">Lokasi Asal:</strong> Tersimpan otomatis sebagai titik keberangkatan saat Anda membuat pengiriman baru.</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Panduan Praktis Pascapanen --}}
                    <div class="agri-card p-6 bg-amber-50/60 border border-amber-200/80 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-base text-slate-900">Panduan Penanganan Panen</h3>
                                <p class="text-xs text-amber-800 font-bold">Tips menjaga kualitas sebelum pengiriman</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs text-slate-700">
                            <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200/70">
                                <p class="font-extrabold text-slate-900">Catat Segera Setelah Panen</p>
                                <p class="text-slate-600 mt-0.5">Pencatatan yang tepat waktu membantu perhitungan usia panen dan sisa masa simpan tetap konsisten.</p>
                            </div>

                            <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200/70">
                                <p class="font-extrabold text-slate-900">Simpan di Area Teduh</p>
                                <p class="text-slate-600 mt-0.5">Hindari paparan sinar matahari langsung pada hasil panen sebelum dimuat ke kendaraan pengiriman.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('createHarvestForm');
            const btn = document.getElementById('submitHarvestBtn');
            const spinner = document.getElementById('harvestSubmitSpinner');
            const btnText = document.getElementById('harvestSubmitText');

            if (form && btn) {
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    spinner?.classList.remove('hidden');
                    if (btnText) btnText.textContent = 'Menyimpan...';
                });
            }
        });
    </script>
</x-app-layout>