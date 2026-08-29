<x-app-layout>
    {{-- Custom Styles & Animations --}}
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
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 animate-card w-full">
        
        {{-- Back Navigation & Page Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('shipments.index') }}" 
                   class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Shipments</span>
                </a>

                <span class="text-slate-300">•</span>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-xs font-bold tracking-wide shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span>LOGISTICS DISPATCH PORTAL</span>
                </div>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Add New Shipment</h1>
            <p class="text-slate-500 mt-1 font-medium text-sm">Hubungkan hasil panen Anda dengan jaringan distribusi pasar modern.</p>
        </div>

        {{-- Grid System 2 Kolom (Form 7-col + Panel Edukasi/Logistik 5-col) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- KOLOM KIRI: Form Input (lg:col-span-7) --}}
            <div class="lg:col-span-7">
                <div class="agri-card p-6 sm:p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-500 via-indigo-600 to-indigo-700"></div>

                    <form action="{{ route('shipments.store') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Harvest Commodity Selection --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Harvest Commodity
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">🚚</span>
                                    <select id="harvestSelect" 
                                            name="harvest_id" 
                                            required
                                            class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-sm p-0">
                                        @foreach($harvests as $harvest)
                                            <option value="{{ $harvest->id }}" 
                                                    data-origin="{{ $harvest->location }}"
                                                    data-weight="{{ $harvest->weight }}">
                                                {{ $harvest->commodity }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('harvest_id')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Origin, Weight & Destination Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            
                            {{-- Origin Input (Auto-filled) --}}
                            <div>
                                <div class="agri-input-group p-4 bg-slate-100/70 border-slate-200">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Origin (From Harvest Location)
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 font-bold text-sm">📍</span>
                                        <input type="text" 
                                               id="originInput" 
                                               name="origin" 
                                               readonly 
                                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold text-sm p-0">
                                    </div>
                                </div>
                                @error('origin')
                                    <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Weight Input (Auto-filled from Harvest Yield) --}}
                            <div>
                                <div class="agri-input-group p-4 bg-slate-100/70 border-slate-200">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Total Weight Yield (KG)
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 font-bold text-sm">⚖️</span>
                                        <input type="text" 
                                               id="weightInput" 
                                               name="weight" 
                                               readonly 
                                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold text-sm p-0">
                                    </div>
                                </div>
                                @error('weight')
                                    <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Destination Input --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Destination City / Warehouse
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">🏁</span>
                                    <input type="text" 
                                           name="destination" 
                                           value="{{ old('destination') }}"
                                           placeholder="e.g. Surabaya Port Terminal" 
                                           required
                                           class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-sm p-0 placeholder-slate-300">
                                </div>
                            </div>
                            @error('destination')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Status Input --}}
                        <div>
                            <div class="agri-input-group p-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Initial Logistics Status
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold text-sm">🔄</span>
                                    <select name="status" 
                                            required
                                            class="w-full bg-transparent border-0 focus:ring-0 text-slate-900 font-bold text-sm p-0">
                                        <option value="Harvested">Harvested</option>
                                        <option value="Packed">Packed</option>
                                        <option value="In Transit">In Transit</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                </div>
                            </div>
                            @error('status')
                                <p class="text-xs font-bold text-rose-600 mt-1.5 ml-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-3">
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-indigo-600 via-indigo-700 to-teal-600 text-white py-4 rounded-2xl font-black text-sm tracking-wide transition-all hover:from-indigo-700 hover:to-teal-700 hover:shadow-lg hover:shadow-indigo-600/20 hover:-translate-y-0.5 active:translate-y-0">
                                Save Shipment Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KOLOM KANAN: Logistics & Farmer Insights Panel (lg:col-span-5) --}}
            <div class="lg:col-span-5 space-y-5">
                
                {{-- Card 1: Perspektif Logistik Expert --}}
                <div class="agri-card p-6 border border-indigo-100 bg-indigo-50/40 relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg border border-indigo-200">
                            🧠
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Logistics AI Monitoring</h3>
                            <p class="text-[11px] text-indigo-600 font-bold">Mitigasi Risiko Rantai Pasok</p>
                        </div>
                    </div>

                    <ul class="space-y-3.5 text-xs text-slate-700 font-medium">
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Autofill Data Panen:</strong> Lokasi (Origin) dan Berat (KG) terkunci otomatis dari data panen untuk integritas *supply chain*.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Estimasi Transit:</strong> Sistem AI menghitung waktu tempuh optimum agar komoditas tiba dalam kondisi segar.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-600 font-black mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Status Real-time:</strong> Pembaruan status berkala (*Harvested* hingga *Delivered*) meningkatkan transparansi ke pembeli.</span>
                        </li>
                    </ul>
                </div>

                {{-- Card 2: Panduan Praktis untuk Petani --}}
                <div class="agri-card p-6 border border-slate-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-lg border border-teal-200">
                            🌾
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Tips Pengiriman Petani</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Panduan sebelum barang diberangkatkan</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700 font-medium">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <span class="text-lg">📦</span>
                            <div>
                                <p class="font-extrabold text-slate-900 text-[11px]">Standar Pengemasan</p>
                                <p class="text-[10px] text-slate-600">Gunakan wadah berventilasi untuk menjaga sirkulasi udara komoditas.</p>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <span class="text-lg">🚛</span>
                            <div>
                                <p class="font-extrabold text-slate-900 text-[11px]">Verifikasi Tujuan</p>
                                <p class="text-[10px] text-slate-600">Pastikan titik Destination sesuai dengan Purchase Order (PO) mitra.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Script Autofill Location Origin & Weight --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const harvestSelect = document.getElementById('harvestSelect');
            const originInput = document.getElementById('originInput');
            const weightInput = document.getElementById('weightInput');

            function updateHarvestDetails() {
                if (harvestSelect && harvestSelect.options.length > 0) {
                    const selectedOption = harvestSelect.options[harvestSelect.selectedIndex];
                    
                    // Autofill Origin
                    originInput.value = selectedOption.dataset.origin || '';
                    
                    // Autofill Weight dengan penambahan format KG
                    const weightVal = selectedOption.dataset.weight;
                    weightInput.value = weightVal ? `${parseFloat(weightVal).toLocaleString('id-ID')} KG` : '';
                }
            }

            // Jalankan saat pertama kali halaman dibuka
            updateHarvestDetails();

            // Event listener saat komoditas diubah
            if (harvestSelect) {
                harvestSelect.addEventListener('change', updateHarvestDetails);
            }
        });
    </script>
</x-app-layout>