<x-app-layout>
    {{-- Custom Styles & Animation --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-table-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-table-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
        }
    </style>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 animate-card">
        
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide mb-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>AGRICULTURAL OUTPUT LOG</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Harvest Records</h1>
                <p class="text-slate-500 mt-1 font-medium text-sm">Log, track, and manage recorded field harvest data for downstream shipment decisions.</p>
            </div>

            <a href="{{ route('harvests.create') }}" 
               class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-3.5 rounded-2xl font-extrabold text-sm transition-all hover:from-emerald-700 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
                <span>Add New Harvest</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            </a>
        </div>

        {{-- Main Container Card --}}
        <div class="agri-card p-6 sm:p-8">
            
            {{-- Flash Session Success Notification --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 font-bold text-sm rounded-2xl border border-emerald-200/80 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    </div>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Table Wrapper --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-slate-500 font-extrabold text-[11px] uppercase tracking-wider">
                            <th class="px-6 py-3">Commodity</th>
                            <th class="px-6 py-3">Weight Yield</th>
                            <th class="px-6 py-3">Location</th>
                            <th class="px-6 py-3">Harvest Date</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($harvests as $index => $harvest)
                            @php
                                // Variasi warna baris bertema AgriFlow AI (Emerald, Teal, Indigo, Cyan)
                                $styles = [
                                    0 => [
                                        'bg' => 'bg-emerald-50/60 hover:bg-emerald-50',
                                        'border' => 'border-emerald-200/80',
                                        'icon_bg' => 'bg-emerald-100 border-emerald-200 text-emerald-700',
                                        'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'dot' => 'bg-emerald-500'
                                    ],
                                    1 => [
                                        'bg' => 'bg-teal-50/60 hover:bg-teal-50',
                                        'border' => 'border-teal-200/80',
                                        'icon_bg' => 'bg-teal-100 border-teal-200 text-teal-700',
                                        'badge' => 'bg-teal-100 text-teal-800 border-teal-200',
                                        'dot' => 'bg-teal-500'
                                    ],
                                    2 => [
                                        'bg' => 'bg-indigo-50/60 hover:bg-indigo-50',
                                        'border' => 'border-indigo-200/80',
                                        'icon_bg' => 'bg-indigo-100 border-indigo-200 text-indigo-700',
                                        'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'dot' => 'bg-indigo-500'
                                    ],
                                    3 => [
                                        'bg' => 'bg-cyan-50/60 hover:bg-cyan-50',
                                        'border' => 'border-cyan-200/80',
                                        'icon_bg' => 'bg-cyan-100 border-cyan-200 text-cyan-700',
                                        'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                        'dot' => 'bg-cyan-500'
                                    ]
                                ];

                                $theme = $styles[$index % 4];
                            @endphp

                        <tr class="agri-table-row {{ $theme['bg'] }} border {{ $theme['border'] }} rounded-2xl overflow-hidden">
                            
                            {{-- Commodity Name (Diseragamkan Menggunakan Icon 🌱) --}}
                            <td class="px-6 py-5 font-black text-slate-900 text-base rounded-l-2xl border-l border-t border-b {{ $theme['border'] }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $theme['icon_bg'] }} border flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                        🌱
                                    </div>
                                    <span class="truncate">{{ $harvest->commodity }}</span>
                                </div>
                            </td>

                            {{-- Weight Yield Badge --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border {{ $theme['badge'] }} text-xs font-black tracking-wide shadow-sm">
                                    <span>⚖️</span>
                                    <span>{{ number_format($harvest->weight, 0) }} KG</span>
                                </span>
                            </td>

                            {{-- Location --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <div class="flex items-center gap-2 text-slate-700 text-sm font-bold">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $theme['dot'] }}"></span>
                                    <span>{{ $harvest->location }}</span>
                                </div>
                            </td>

                            {{-- Harvest Date --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <span class="text-slate-600 font-mono text-xs font-bold bg-white/80 px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-sm">
                                    📅 {{ \Carbon\Carbon::parse($harvest->harvest_date)->format('M d, Y') }}
                                </span>
                            </td>

                            {{-- Delete Action Button --}}
                            <td class="px-6 py-5 text-right rounded-r-2xl border-r border-t border-b {{ $theme['border'] }}">
                                <form action="{{ route('harvests.destroy', $harvest->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data panen ini?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2.5 rounded-xl bg-white/90 border border-slate-200/80 text-slate-400 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all group shadow-sm"
                                            title="Delete record">
                                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center bg-slate-50/50 rounded-2xl border border-slate-200/80">
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto text-xl shadow-sm">
                                        📦
                                    </div>
                                    <p class="text-slate-600 font-bold text-sm">No harvest records found.</p>
                                    <p class="text-slate-400 text-xs">Start logging your agricultural outputs by clicking the button above.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>