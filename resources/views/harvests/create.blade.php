<x-app-layout>
    {{-- Custom Styles & Micro-Interactions --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
        }

        .agri-input-group {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-input-group:focus-within {
            background: #ffffff;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 animate-card w-full">
        
        {{-- Back Navigation & Page Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('harvests.index') }}" 
                   class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Harvest Records</span>
                </a>

                <span class="text-slate-300">•</span>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>HARVEST LOGGING PORTAL</span>
                </div>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Add New Harvest</h1>
            <p class="text-slate-500 mt-1 font-medium text-sm">Catat dan dokumentasikan hasil panen lapangan secara akurat untuk mempermudah distribusi pasar.</p>
        </div>

        {{-- Grid System 2 Kolom (Form 7-col + Panel Panduan/Analisis 5-col) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- KOLOM KIRI: Form Input (lg:col-span-7) --}}
            <div class="lg:col-span-7">
                <div class="agri-card p-6 sm:p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600"></div>

                    <form action="{{ route('harvests.store') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Commodity Name Input --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Commodity Name
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">🌱</span>
                                    <input type="text" 
                                           name="commodity" 
                                           value="{{ old('commodity') }}"
                                           placeholder="e.g. Arabica Coffee, Tomat Fresh" 
                                           required
                                           class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-base p-0 placeholder-slate-300">
                                </div>
                            </div>
                            @error('commodity')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Weight (KG) Input --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Total Weight Yield (KG)
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">⚖️</span>
                                    <input type="number" 
                                           step="0.01"
                                           name="weight" 
                                           value="{{ old('weight') }}"
                                           placeholder="0.00" 
                                           required
                                           class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-base p-0 placeholder-slate-300">
                                </div>
                            </div>
                            @error('weight')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location Input --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Farm / Warehouse Location
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">📍</span>
                                    <input type="text" 
                                           name="location" 
                                           value="{{ old('location') }}"
                                           placeholder="e.g. Block A3, Lembang Farm" 
                                           required
                                           class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-base p-0 placeholder-slate-300">
                                </div>
                            </div>
                            @error('location')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date Grid (Harvest & Expiry) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <div class="agri-input-group p-4">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Harvest Date
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 font-bold text-sm">📅</span>
                                        <input type="date" 
                                               name="harvest_date" 
                                               value="{{ old('harvest_date') }}"
                                               required
                                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-sm p-0 uppercase">
                                    </div>
                                </div>
                                @error('harvest_date')
                                    <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="agri-input-group p-4">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Expected Expiry Date
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 font-bold text-sm">⏳</span>
                                        <input type="date" 
                                               name="expiry_date" 
                                               value="{{ old('expiry_date') }}"
                                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-sm p-0 uppercase">
                                    </div>
                                </div>
                                @error('expiry_date')
                                    <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-3">
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-4 rounded-2xl font-black text-sm tracking-wide transition-all hover:from-emerald-700 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5 active:translate-y-0">
                                Save Harvest Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KOLOM KANAN: Agricultural & Quality Intelligence Panel (lg:col-span-5) --}}
            <div class="lg:col-span-5 space-y-5">
                
                {{-- Card 1: Perspektif Pakar Logistik & Agrikultur (AI Quality Control) --}}
                <div class="agri-card p-6 border border-emerald-100 bg-emerald-50/40 relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg border border-emerald-200">
                            🧠
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Harvest Data Foundation</h3>
                            <p class="text-[11px] text-emerald-700 font-bold">Input operasional untuk analisis pascapanen</p>
                        </div>
                    </div>

                    <ul class="space-y-3.5 text-xs text-slate-700 font-medium">
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Recorded Weight:</strong> Berat panen (KG) menjadi input kuantitas shipment dan perhitungan activity-based road-freight CO₂e.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Recorded Operational Deadline:</strong> Tanggal expiry digunakan sebagai batas operasional yang direkonsiliasi dengan reference shelf life saat tersedia.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Harvest Origin:</strong> Lokasi panen disimpan sebagai konteks asal dan dapat digunakan saat membuat shipment.</span>
                        </li>
                    </ul>
                </div>

                {{-- Card 2: Panduan Praktis Petani (Best Practices Saat Panen) --}}
                <div class="agri-card p-6 border border-slate-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-lg border border-teal-200">
                            🌾
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Panduan Hasil Panen</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Saran penanganan produk pascapanen</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700 font-medium">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <span class="text-lg">☀️</span>
                            <div>
                                <p class="font-extrabold text-slate-900 text-[11px]">Waktu Pencatatan</p>
                                <p class="text-[10px] text-slate-600">Catat sedekat mungkin dengan waktu panen agar harvest age dan recorded operational deadline memiliki konteks waktu yang konsisten.</p>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <span class="text-lg">🌡️</span>
                            <div>
                                <p class="font-extrabold text-slate-900 text-[11px]">Suhu Penyimpanan</p>
                                <p class="text-[10px] text-slate-600">Pastikan komoditas disimpan di area teduh sebelum dimuat ke truk pengiriman.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>