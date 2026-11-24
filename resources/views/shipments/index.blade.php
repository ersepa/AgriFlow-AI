<x-app-layout>
    @php
        $totalActive = $shipments->count();
        $countHarvested = $shipments->where('status', 'Harvested')->count();
        $countPacked = $shipments->where('status', 'Packed')->count();
        $countInTransit = $shipments->where('status', 'In Transit')->count();
    @endphp

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 6px 24px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.07);
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen bg-[#F8FAFC] bg-grid-pattern pb-16 animate-card">
        <div class="max-w-7xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ================= HEADER ================= --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide mb-3 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Operasional Logistik Aktif</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Pengiriman</h1>
                    <p class="text-slate-600 mt-1.5 font-medium text-sm sm:text-base max-w-2xl">
                        Kelola seluruh pengiriman hasil panen yang sedang aktif, pantau rute distribusi, dan perbarui status perjalanan dengan mudah.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="{{ route('completed-shipments.index') }}"
                       class="inline-flex items-center justify-center gap-2 border border-slate-200 bg-white text-slate-700 hover:text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50/50 px-5 py-3 rounded-2xl font-extrabold text-sm transition-all shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Pengiriman Selesai</span>
                    </a>

                    <a href="{{ route('shipments.create') }}"
                       class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-6 py-3 rounded-2xl font-extrabold text-sm transition-all shadow-lg shadow-emerald-600/20 hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Buat Pengiriman</span>
                    </a>
                </div>
            </div>

            {{-- ================= RINGKASAN STATISTIK (4 Kartu Berwarna) ================= --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Pengiriman Aktif --}}
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-emerald-900">Total Pengiriman Aktif</span>
                        <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2m10 0H9m10 0h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/>
                                <circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-emerald-700">{{ $totalActive }}</p>
                        <p class="text-xs font-semibold text-emerald-800/80 mt-0.5">Dalam siklus operasional</p>
                    </div>
                </div>

                {{-- Baru Dipanen --}}
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-amber-900">Baru Dipanen</span>
                        <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-amber-700">{{ $countHarvested }}</p>
                        <p class="text-xs font-semibold text-amber-800/80 mt-0.5">Menunggu pengemasan</p>
                    </div>
                </div>

                {{-- Dikemas / Siap Kirim --}}
                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-blue-900">Siap Kirim</span>
                        <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-blue-700">{{ $countPacked }}</p>
                        <p class="text-xs font-semibold text-blue-800/80 mt-0.5">Menunggu keberangkatan</p>
                    </div>
                </div>

                {{-- Dalam Perjalanan --}}
                <div class="rounded-2xl bg-teal-50 border border-teal-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-teal-900">Dalam Perjalanan</span>
                        <span class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="6" cy="19" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 19h8.5a3.5 3.5 0 000-7h-11a3.5 3.5 0 010-7H15"/><circle cx="18" cy="5" r="3"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-teal-700">{{ $countInTransit }}</p>
                        <p class="text-xs font-semibold text-teal-800/80 mt-0.5">Sedang menuju tujuan</p>
                    </div>
                </div>
            </div>

            {{-- ================= KONTEN UTAMA DAFTAR PENGIRIMAN ================= --}}
            <div class="agri-card p-5 sm:p-7 space-y-6">

                {{-- Notifikasi Flash --}}
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 text-emerald-800 font-bold text-sm rounded-2xl border border-emerald-200 flex items-center gap-3 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                        </div>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-4 bg-amber-50 text-amber-900 font-bold text-sm rounded-2xl border border-amber-200 flex items-center gap-3 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        </div>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                {{-- Filter & Pencarian --}}
                @if($shipments->isNotEmpty())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="shipmentSearchInput"
                                   placeholder="Cari komoditas, asal, atau tujuan..."
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="flex items-center gap-2">
                            <select id="shipmentStatusFilter"
                                    class="w-full sm:w-auto rounded-xl bg-slate-50 border border-slate-200 py-2.5 pl-3.5 pr-9 text-sm font-bold text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="all">Semua Status</option>
                                <option value="Harvested">Baru Dipanen</option>
                                <option value="Packed">Siap Kirim (Dikemas)</option>
                                <option value="In Transit">Dalam Perjalanan</option>
                            </select>
                        </div>
                    </div>
                @endif

                {{-- Daftar Pengiriman --}}
                @if($shipments->isNotEmpty())
                    <div id="shipmentListContainer" class="space-y-3.5">
                        @foreach($shipments as $index => $shipment)
                            @php
                                $commodity = $shipment->harvest->commodity ?? 'Tidak diketahui';
                                $weight = $shipment->harvest->weight ?? null;

                                $statusBadge = match ($shipment->status) {
                                    'Harvested' => [
                                        'label' => 'Baru Dipanen',
                                        'classes' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'dot' => 'bg-amber-500',
                                    ],
                                    'Packed' => [
                                        'label' => 'Siap Kirim',
                                        'classes' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'dot' => 'bg-blue-500',
                                    ],
                                    'In Transit' => [
                                        'label' => 'Dalam Perjalanan',
                                        'classes' => 'bg-teal-100 text-teal-800 border-teal-200',
                                        'dot' => 'bg-teal-500',
                                    ],
                                    'Delivered' => [
                                        'label' => 'Telah Tiba',
                                        'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'dot' => 'bg-emerald-500',
                                    ],
                                    default => [
                                        'label' => $shipment->status,
                                        'classes' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'dot' => 'bg-slate-400',
                                    ],
                                };

                                $rowThemes = [
                                    0 => ['card' => 'bg-emerald-50/55 border-emerald-200/80 hover:bg-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                                    1 => ['card' => 'bg-sky-50/55 border-sky-200/80 hover:bg-sky-50', 'icon' => 'bg-sky-100 text-sky-700 border-sky-200'],
                                    2 => ['card' => 'bg-teal-50/55 border-teal-200/80 hover:bg-teal-50', 'icon' => 'bg-teal-100 text-teal-700 border-teal-200'],
                                    3 => ['card' => 'bg-indigo-50/45 border-indigo-200/80 hover:bg-indigo-50/80', 'icon' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
                                ];
                                $theme = $rowThemes[$index % 4];
                            @endphp

                            <div class="shipment-item agri-row rounded-2xl border p-4 sm:p-5 {{ $theme['card'] }}"
                                 data-search="{{ strtolower($commodity . ' ' . $shipment->origin . ' ' . $shipment->destination . ' #' . $shipment->id) }}"
                                 data-status="{{ $shipment->status }}">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

                                    {{-- Identitas & Komoditas (Col 4) --}}
                                    <div class="lg:col-span-4 flex items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-2xl border {{ $theme['icon'] }} flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-extrabold text-slate-500">#{{ $shipment->id }}</span>
                                                @if($weight !== null)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="text-xs font-bold text-slate-600">{{ number_format((float) $weight, 0, ',', '.') }} kg</span>
                                                @endif
                                            </div>
                                            <p class="font-black text-slate-900 text-base capitalize truncate mt-0.5">
                                                {{ $commodity }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Rute Asal -> Tujuan (Col 4) --}}
                                    <div class="lg:col-span-4 flex flex-col gap-1 min-w-0">
                                        <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            <span class="capitalize truncate">{{ $shipment->origin }}</span>
                                            <span class="text-emerald-600 font-black shrink-0">→</span>
                                            <span class="capitalize truncate">{{ $shipment->destination }}</span>
                                        </div>
                                        @if($shipment->distance_km !== null || $shipment->duration_hours !== null)
                                            <p class="text-xs font-semibold text-slate-500 pl-6">
                                                @if($shipment->distance_km !== null)
                                                    {{ number_format((float) $shipment->distance_km, 0, ',', '.') }} km
                                                @endif
                                                @if($shipment->distance_km !== null && $shipment->duration_hours !== null)
                                                    ·
                                                @endif
                                                @if($shipment->duration_hours !== null)
                                                    Estimasi {{ number_format((float) $shipment->duration_hours, 1, ',', '.') }} jam
                                                @endif
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Status Badge (Col 2) --}}
                                    <div class="lg:col-span-2 flex items-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold border shadow-2xs {{ $statusBadge['classes'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                                            {{ $statusBadge['label'] }}
                                        </span>
                                    </div>

                                    {{-- Aksi (Col 2) --}}
                                    <div class="lg:col-span-2 flex items-center lg:justify-end gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-200/70">
                                        <a href="{{ route('shipments.show', $shipment->id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl border border-emerald-200 font-bold text-xs transition-all shadow-2xs"
                                           title="Lihat Detail Pengiriman">
                                            <span>Detail</span>
                                        </a>

                                        <button type="button"
                                                onclick="openEditModal('{{ route('shipments.update', $shipment->id) }}', '{{ $shipment->status }}')"
                                                class="p-2 bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 rounded-xl border border-slate-200 transition-all shadow-2xs"
                                                title="Perbarui Status">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        <form id="deleteShipmentForm-{{ $shipment->id }}"
                                              action="{{ route('shipments.destroy', $shipment->id) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="openDeleteShipmentModal('{{ $shipment->id }}', @js($commodity), @js($shipment->origin), @js($shipment->destination))"
                                                    class="p-2 bg-white text-slate-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 rounded-xl border border-slate-200 transition-all shadow-2xs"
                                                    title="Hapus Pengiriman">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Empty state hasil filter --}}
                    <div id="shipmentFilterEmpty" class="hidden py-12 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-200">
                        <p class="text-sm font-bold text-slate-700">Tidak ada pengiriman yang cocok dengan pencarian Anda.</p>
                        <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci atau pilih status lain.</p>
                    </div>
                @else
                    {{-- Empty State Utama --}}
                    <div class="py-14 px-6 text-center rounded-3xl bg-gradient-to-br from-emerald-50/70 via-white to-teal-50/70 border border-emerald-200/70">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2m10 0H9m10 0h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/>
                                <circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-900">Belum Ada Pengiriman Aktif</h3>
                        <p class="text-sm text-slate-600 mt-1.5 max-w-md mx-auto">
                            Mulai buat pengiriman pertama Anda untuk memantau rute perjalanan, kondisi kesegaran, dan rekomendasi AgriFlow AI.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('shipments.create') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/20 transition-all">
                                + Buat Pengiriman Pertama
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ================= MODAL PERBARUI STATUS ================= --}}
    <div id="editModal" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl animate-card">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wider font-extrabold text-emerald-700">Tahapan Pengiriman</p>
                        <h2 class="font-black text-slate-900 text-lg">Perbarui Status</h2>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    ✕
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-5" onsubmit="return handleShipmentStatusSubmit(event)">
                @csrf
                @method('PATCH')

                <div>
                    <label for="statusSelect" class="block text-xs font-extrabold text-slate-700 mb-2">
                        Pilih Status Pengiriman Terbaru
                    </label>
                    <select id="statusSelect" name="status"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="Harvested">Baru Dipanen</option>
                        <option value="Packed">Siap Kirim (Dikemas)</option>
                        <option value="In Transit">Dalam Perjalanan</option>
                        <option value="Delivered">Telah Tiba (Selesai)</option>
                    </select>

                    <div class="mt-3 flex items-start gap-2.5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs leading-relaxed text-slate-600">
                            Menandai pengiriman sebagai <strong>Telah Tiba</strong> akan menutup analisis aktif, optimasi rute, dan simulasi Digital Twin. Riwayat data tetap tersimpan di arsip.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                            class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-2xl font-extrabold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-1/2 bg-emerald-600 hover:bg-emerald-700 text-white py-3.5 rounded-2xl font-extrabold text-xs transition-all shadow-md shadow-emerald-600/20">
                        Simpan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL KONFIRMASI TELAH TIBA ================= --}}
    <div id="deliveredConfirmModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-card">
            <div class="p-7 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">Selesaikan Pengiriman</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">Tandai sebagai Telah Tiba?</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Pengiriman ini akan keluar dari daftar aktif dan dipindahkan ke arsip <strong>Pengiriman Selesai</strong>.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-2.5 text-xs text-slate-600">
                    <p class="font-extrabold text-slate-800">Yang akan terjadi selanjutnya:</p>
                    <p>• Riwayat analisis dan data kondisi yang tercatat tetap tersimpan aman.</p>
                    <p>• Ringkasan akhir pengiriman akan diarsipkan untuk peninjauan.</p>
                    <p>• Analisis baru, pembaruan kondisi, dan simulasi Digital Twin akan ditutup.</p>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" onclick="closeDeliveredConfirmModal()"
                            class="sm:w-1/2 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50 transition">
                        Tetap Aktif
                    </button>
                    <button type="button" onclick="confirmDeliveredShipment()"
                            class="sm:w-1/2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 px-4 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-emerald-600/20 transition">
                        Tandai Telah Tiba
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL KONFIRMASI HAPUS ================= --}}
    <div id="deleteShipmentModal" class="fixed inset-0 z-[10010] hidden items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-card">
            <div class="p-7 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-rose-600">Hapus Pengiriman</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">Hapus data pengiriman ini?</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Anda akan menghapus secara permanen pengiriman <span id="deleteShipmentCommodity" class="font-bold text-slate-900">ini</span>.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-rose-100 bg-rose-50/70 p-4">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Rute Pengiriman</p>
                    <p id="deleteShipmentRoute" class="mt-1 text-sm font-bold text-slate-900">—</p>
                    <p class="mt-2 text-xs text-slate-600">
                        Tindakan ini hanya disarankan untuk data yang keliru. Pengiriman yang telah selesai sebaiknya disimpan di arsip.
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeDeleteShipmentModal()"
                            class="w-1/2 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" onclick="confirmDeleteShipment()"
                            class="w-1/2 rounded-2xl bg-rose-600 hover:bg-rose-700 px-4 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-rose-600/20 transition">
                        Hapus Pengiriman
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        let pendingDeleteShipmentId = null;

        function openEditModal(url, currentStatus) {
            const form = document.getElementById('editForm');
            const statusSelect = document.getElementById('statusSelect');
            form.action = url;
            if (currentStatus) {
                statusSelect.value = currentStatus;
            }
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function handleShipmentStatusSubmit(event) {
            const status = document.getElementById('statusSelect').value;
            if (status !== 'Delivered') {
                return true;
            }
            event.preventDefault();
            document.getElementById('deliveredConfirmModal').style.display = 'flex';
            return false;
        }

        function closeDeliveredConfirmModal() {
            document.getElementById('deliveredConfirmModal').style.display = 'none';
        }

        function confirmDeliveredShipment() {
            const form = document.getElementById('editForm');
            closeDeliveredConfirmModal();
            HTMLFormElement.prototype.submit.call(form);
        }

        function openDeleteShipmentModal(id, commodity, origin, destination) {
            pendingDeleteShipmentId = id;
            document.getElementById('deleteShipmentCommodity').textContent = commodity || 'pengiriman ini';
            document.getElementById('deleteShipmentRoute').textContent =
                `${origin || 'Asal tidak diketahui'} → ${destination || 'Tujuan tidak diketahui'}`;
            document.getElementById('deleteShipmentModal').style.display = 'flex';
        }

        function closeDeleteShipmentModal() {
            pendingDeleteShipmentId = null;
            document.getElementById('deleteShipmentModal').style.display = 'none';
        }

        function confirmDeleteShipment() {
            if (!pendingDeleteShipmentId) return;
            const form = document.getElementById(`deleteShipmentForm-${pendingDeleteShipmentId}`);
            if (!form) {
                closeDeleteShipmentModal();
                return;
            }
            HTMLFormElement.prototype.submit.call(form);
        }

        window.addEventListener('click', function (event) {
            const editModal = document.getElementById('editModal');
            const deliveredModal = document.getElementById('deliveredConfirmModal');
            const deleteModal = document.getElementById('deleteShipmentModal');
            if (event.target === deliveredModal) return closeDeliveredConfirmModal();
            if (event.target === deleteModal) return closeDeleteShipmentModal();
            if (event.target === editModal) closeEditModal();
        });

        window.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            const deliveredModal = document.getElementById('deliveredConfirmModal');
            const deleteModal = document.getElementById('deleteShipmentModal');
            const editModal = document.getElementById('editModal');
            if (deliveredModal.style.display === 'flex') return closeDeliveredConfirmModal();
            if (deleteModal.style.display === 'flex') return closeDeleteShipmentModal();
            if (editModal.style.display === 'flex') closeEditModal();
        });

        // Pencarian & Filter Status (Client-Side)
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('shipmentSearchInput');
            const statusFilter = document.getElementById('shipmentStatusFilter');
            const items = document.querySelectorAll('.shipment-item');
            const emptyFilterEl = document.getElementById('shipmentFilterEmpty');

            if (!searchInput || !statusFilter || items.length === 0) return;

            function applyFilter() {
                const keyword = searchInput.value.trim().toLowerCase();
                const status = statusFilter.value;
                let visibleCount = 0;

                items.forEach(item => {
                    const text = item.dataset.search || '';
                    const itemStatus = item.dataset.status || '';
                    const matchKeyword = !keyword || text.includes(keyword);
                    const matchStatus = status === 'all' || itemStatus === status;

                    if (matchKeyword && matchStatus) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (emptyFilterEl) {
                    emptyFilterEl.classList.toggle('hidden', visibleCount > 0);
                }
            }

            searchInput.addEventListener('input', applyFilter);
            statusFilter.addEventListener('change', applyFilter);
        });
    </script>
</x-app-layout>