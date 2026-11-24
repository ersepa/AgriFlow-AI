<x-app-layout>
    @php
        $totalHarvests = $harvests->count();
        $totalWeight = (float) $harvests->sum('weight');
        $uniqueCommodities = $harvests->pluck('commodity')->filter()->map(fn ($c) => strtolower(trim($c)))->unique()->count();

        $now = \Carbon\Carbon::now()->startOfDay();
        $nearExpiryCount = $harvests->filter(function ($h) use ($now) {
            if (!$h->expiry_date) return false;
            $daysLeft = $now->diffInDays(\Carbon\Carbon::parse($h->expiry_date)->startOfDay(), false);
            return $daysLeft >= 0 && $daysLeft <= 3;
        })->count();

        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatDateIndo = function ($date) use ($bulanIndo): string {
            if (!$date) return 'Belum diatur';
            $c = \Carbon\Carbon::parse($date);
            return $c->format('d') . ' ' . ($bulanIndo[(int) $c->format('n')] ?? $c->format('M')) . ' ' . $c->format('Y');
        };
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide mb-3 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Catatan Produksi Pertanian</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Hasil Panen</h1>
                    <p class="text-slate-600 mt-1.5 font-medium text-sm sm:text-base max-w-2xl">
                        Catat, pantau, dan kelola seluruh hasil panen Anda sebagai dasar perencanaan pengiriman dan kesegaran produk.
                    </p>
                </div>

                <a href="{{ route('harvests.create') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-6 py-3.5 rounded-2xl font-extrabold text-sm transition-all shadow-lg shadow-emerald-600/20 hover:-translate-y-0.5 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Hasil Panen</span>
                </a>
            </div>

            {{-- ================= RINGKASAN STATISTIK (4 Kartu Berwarna) ================= --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Hasil Panen --}}
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-emerald-900">Total Hasil Panen</span>
                        <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-emerald-700">{{ $totalHarvests }}</p>
                        <p class="text-xs font-semibold text-emerald-800/80 mt-0.5">Batch panen tercatat</p>
                    </div>
                </div>

                {{-- Total Berat Panen --}}
                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-blue-900">Total Berat Panen</span>
                        <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl sm:text-3xl font-black text-blue-700 truncate">{{ number_format($totalWeight, 0, ',', '.') }} <span class="text-sm font-bold">kg</span></p>
                        <p class="text-xs font-semibold text-blue-800/80 mt-0.5">Akumulasi volume panen</p>
                    </div>
                </div>

                {{-- Jumlah Komoditas --}}
                <div class="rounded-2xl bg-teal-50 border border-teal-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-teal-900">Jenis Komoditas</span>
                        <span class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-teal-700">{{ $uniqueCommodities }}</p>
                        <p class="text-xs font-semibold text-teal-800/80 mt-0.5">Komoditas berbeda</p>
                    </div>
                </div>

                {{-- Mendekati Kedaluwarsa --}}
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-extrabold text-amber-900">Mendekati Batas</span>
                        <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-black text-amber-700">{{ $nearExpiryCount }}</p>
                        <p class="text-xs font-semibold text-amber-800/80 mt-0.5">Batas simpan ≤ 3 hari</p>
                    </div>
                </div>
            </div>

            {{-- ================= KONTEN UTAMA DAFTAR HASIL PANEN ================= --}}
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

                {{-- Pencarian & Filter --}}
                @if($harvests->isNotEmpty())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="harvestSearchInput"
                                   placeholder="Cari komoditas atau lokasi panen..."
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="flex items-center gap-2">
                            <select id="harvestConditionFilter"
                                    class="w-full sm:w-auto rounded-xl bg-slate-50 border border-slate-200 py-2.5 pl-3.5 pr-9 text-sm font-bold text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="all">Semua Kondisi</option>
                                <option value="safe">Layak Simpan</option>
                                <option value="near">Mendekati Kedaluwarsa</option>
                                <option value="expired">Melewati Batas</option>
                            </select>
                        </div>
                    </div>
                @endif

                {{-- Daftar Hasil Panen --}}
                @if($harvests->isNotEmpty())
                    <div class="space-y-3.5">
                        @foreach($harvests as $index => $harvest)
                            @php
                                $daysLeft = $harvest->expiry_date
                                    ? $now->diffInDays(\Carbon\Carbon::parse($harvest->expiry_date)->startOfDay(), false)
                                    : null;

                                if ($daysLeft === null) {
                                    $condKey = 'safe';
                                    $condBadge = ['label' => 'Batas Belum Diatur', 'classes' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400'];
                                } elseif ($daysLeft < 0) {
                                    $condKey = 'expired';
                                    $condBadge = ['label' => 'Melewati Batas', 'classes' => 'bg-rose-100 text-rose-800 border-rose-200', 'dot' => 'bg-rose-500'];
                                } elseif ($daysLeft <= 3) {
                                    $condKey = 'near';
                                    $condBadge = ['label' => 'Sisa ' . $daysLeft . ' Hari', 'classes' => 'bg-amber-100 text-amber-900 border-amber-200', 'dot' => 'bg-amber-500'];
                                } else {
                                    $condKey = 'safe';
                                    $condBadge = ['label' => 'Layak (' . $daysLeft . ' Hari)', 'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500'];
                                }

                                $rowThemes = [
                                    0 => ['card' => 'bg-emerald-50/55 border-emerald-200/80 hover:bg-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                                    1 => ['card' => 'bg-teal-50/55 border-teal-200/80 hover:bg-teal-50', 'icon' => 'bg-teal-100 text-teal-700 border-teal-200'],
                                    2 => ['card' => 'bg-sky-50/55 border-sky-200/80 hover:bg-sky-50', 'icon' => 'bg-sky-100 text-sky-700 border-sky-200'],
                                    3 => ['card' => 'bg-amber-50/45 border-amber-200/80 hover:bg-amber-50/80', 'icon' => 'bg-amber-100 text-amber-700 border-amber-200'],
                                ];
                                $theme = $rowThemes[$index % 4];
                            @endphp

                            <div class="harvest-item agri-row rounded-2xl border p-4 sm:p-5 {{ $theme['card'] }}"
                                 data-search="{{ strtolower(($harvest->commodity ?? '') . ' ' . ($harvest->location ?? '')) }}"
                                 data-condition="{{ $condKey }}">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

                                    {{-- Komoditas & Berat (Col 4) --}}
                                    <div class="lg:col-span-4 flex items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-2xl border {{ $theme['icon'] }} flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-slate-900 text-base capitalize truncate">{{ $harvest->commodity }}</p>
                                            <p class="text-xs font-extrabold text-emerald-700 mt-0.5">
                                                {{ number_format((float) $harvest->weight, 0, ',', '.') }} kg
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Lokasi Panen (Col 3) --}}
                                    <div class="lg:col-span-3 flex items-center gap-2 text-sm font-bold text-slate-700 min-w-0">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span class="capitalize truncate">{{ $harvest->location }}</span>
                                    </div>

                                    {{-- Tanggal Panen & Kedaluwarsa (Col 2) --}}
                                    <div class="lg:col-span-2 text-xs space-y-1">
                                        <p class="font-bold text-slate-700">
                                            Panen: <span class="text-slate-900">{{ $formatDateIndo($harvest->harvest_date) }}</span>
                                        </p>
                                        <p class="font-semibold text-slate-500">
                                            Batas: <span class="{{ $condKey === 'expired' ? 'text-rose-600 font-bold' : 'text-slate-700' }}">{{ $formatDateIndo($harvest->expiry_date) }}</span>
                                        </p>
                                    </div>

                                    {{-- Status Kesegaran (Col 2) --}}
                                    <div class="lg:col-span-2 flex items-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold border shadow-2xs {{ $condBadge['classes'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $condBadge['dot'] }}"></span>
                                            {{ $condBadge['label'] }}
                                        </span>
                                    </div>

                                    {{-- Aksi Hapus (Col 1) --}}
                                    <div class="lg:col-span-1 flex items-center lg:justify-end pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-200/70">
                                        <form action="{{ route('harvests.destroy', $harvest->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="openDeleteHarvestModal(
                                                        this.form,
                                                        @js($harvest->commodity),
                                                        @js($harvest->location),
                                                        @js(number_format((float) $harvest->weight, 0, ',', '.') . ' kg'),
                                                        @js($formatDateIndo($harvest->harvest_date))
                                                    )"
                                                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all shadow-2xs"
                                                    title="Hapus Hasil Panen">
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

                    {{-- Empty state filter --}}
                    <div id="harvestFilterEmpty" class="hidden py-12 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-200">
                        <p class="text-sm font-bold text-slate-700">Tidak ada data panen yang cocok dengan pencarian Anda.</p>
                        <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci atau pilih filter kondisi lain.</p>
                    </div>
                @else
                    {{-- Empty State Utama --}}
                    <div class="py-14 px-6 text-center rounded-3xl bg-gradient-to-br from-emerald-50/70 via-white to-teal-50/70 border border-emerald-200/70">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-900">Belum Ada Data Hasil Panen</h3>
                        <p class="text-sm text-slate-600 mt-1.5 max-w-md mx-auto">
                            Mulai catat hasil panen pertama Anda agar mudah dihubungkan ke pengiriman dan analisis kesegaran AgriFlow AI.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('harvests.create') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/20 transition-all">
                                + Tambah Hasil Panen Pertama
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ================= MODAL KONFIRMASI HAPUS HASIL PANEN ================= --}}
    <div id="deleteHarvestModal" class="fixed inset-0 z-[10010] hidden items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-card">
            <div class="p-7 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-rose-600">Hapus Hasil Panen</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">Hapus data panen ini?</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Anda akan menghapus secara permanen data panen <span id="deleteHarvestCommodity" class="font-bold text-slate-900">ini</span>.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-rose-100 bg-rose-50/70 p-4 space-y-3">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Informasi Hasil Panen</p>
                    <div>
                        <p class="text-xs text-slate-500">Lokasi Panen</p>
                        <p id="deleteHarvestLocation" class="text-sm font-bold text-slate-900 mt-0.5">—</p>
                    </div>
                    <div class="flex justify-between gap-4">
                        <div>
                            <p class="text-xs text-slate-500">Berat Panen</p>
                            <p id="deleteHarvestWeight" class="text-sm font-bold text-slate-900 mt-0.5">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Tanggal Panen</p>
                            <p id="deleteHarvestDate" class="text-sm font-bold text-slate-900 mt-0.5">—</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 pt-1">
                        Pastikan data panen ini sudah tidak digunakan oleh pengiriman yang masih berjalan.
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeDeleteHarvestModal()"
                            class="w-1/2 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" onclick="confirmDeleteHarvest()"
                            class="w-1/2 rounded-2xl bg-rose-600 hover:bg-rose-700 px-4 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-rose-600/20 transition">
                        Hapus Hasil Panen
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        let pendingDeleteHarvestForm = null;

        function openDeleteHarvestModal(form, commodity, location, weight, date) {
            pendingDeleteHarvestForm = form;
            document.getElementById('deleteHarvestCommodity').textContent = commodity || 'hasil panen ini';
            document.getElementById('deleteHarvestLocation').textContent = location || 'Lokasi tidak diketahui';
            document.getElementById('deleteHarvestWeight').textContent = weight || '—';
            document.getElementById('deleteHarvestDate').textContent = date || '—';
            document.getElementById('deleteHarvestModal').style.display = 'flex';
        }

        function closeDeleteHarvestModal() {
            pendingDeleteHarvestForm = null;
            document.getElementById('deleteHarvestModal').style.display = 'none';
        }

        function confirmDeleteHarvest() {
            if (!pendingDeleteHarvestForm) return;
            const form = pendingDeleteHarvestForm;
            pendingDeleteHarvestForm = null;
            document.getElementById('deleteHarvestModal').style.display = 'none';
            HTMLFormElement.prototype.submit.call(form);
        }

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('deleteHarvestModal');
            if (event.target === modal) closeDeleteHarvestModal();
        });

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeDeleteHarvestModal();
        });

        // Pencarian & Filter Kondisi (Client-Side)
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('harvestSearchInput');
            const conditionFilter = document.getElementById('harvestConditionFilter');
            const items = document.querySelectorAll('.harvest-item');
            const emptyFilterEl = document.getElementById('harvestFilterEmpty');

            if (!searchInput || !conditionFilter || items.length === 0) return;

            function applyFilter() {
                const keyword = searchInput.value.trim().toLowerCase();
                const cond = conditionFilter.value;
                let visibleCount = 0;

                items.forEach(item => {
                    const text = item.dataset.search || '';
                    const itemCond = item.dataset.condition || '';
                    const matchKeyword = !keyword || text.includes(keyword);
                    const matchCond = cond === 'all' || itemCond === cond;

                    if (matchKeyword && matchCond) {
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
            conditionFilter.addEventListener('change', applyFilter);
        });
    </script>
</x-app-layout>