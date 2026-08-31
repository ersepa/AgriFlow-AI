<x-app-layout>
    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
            <div>
                <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-600">
                    Operational Digital Twin
                </p>
                <h1 class="text-3xl font-black text-slate-900 mt-2 tracking-wide">
                    Comparison History
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Stored multi-scenario decision snapshots with engine version and preferred decision.
                </p>
            </div>

            <a href="{{ route('digital-twin.index') }}"
               class="inline-flex items-center gap-2 text-slate-500 hover:text-cyan-600 transition-all hover:-translate-x-1 font-bold text-xs uppercase tracking-widest">
                ← Back to Digital Twin
            </a>
        </div>

        {{-- HISTORY CONTENT CONTAINER --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(15,23,42,0.04)] overflow-hidden">
            @forelse($comparisonSets as $set)
                <a href="{{ route('digital-twin.comparisons.show', $set) }}"
                   class="grid grid-cols-1 md:grid-cols-6 gap-4 p-6 border-b border-slate-100 last:border-0 hover:bg-slate-50/80 transition-colors duration-200 items-center group">
                    
                    {{-- Set Identity & Commodity --}}
                    <div class="md:col-span-2">
                        <p class="font-black text-slate-900 text-base group-hover:text-cyan-600 transition-colors">
                            {{ $set->name }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1 font-medium">
                            #{{ $set->shipment_id }} · <span class="text-slate-800 capitalize font-bold">{{ $set->shipment?->harvest?->commodity ?? 'Unknown' }}</span>
                        </p>
                    </div>

                    {{-- Scenarios Count --}}
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Scenarios</p>
                        <p class="font-black text-slate-900 mt-1 text-sm">
                            {{ count($set->scenarios_snapshot ?? []) }}
                        </p>
                    </div>

                    {{-- Preferred Option Badge --}}
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest mb-1">Preferred</p>
                        <span class="inline-flex px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200/80 text-[11px] font-extrabold uppercase tracking-wider shadow-sm">
                            {{ $set->preferred_option === 'current_plan'
                                ? 'Current Plan'
                                : ($set->preferred_option ?? '—') }}
                        </span>
                    </div>

                    {{-- Evidence Coverage --}}
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Evidence</p>
                        <p class="font-black text-cyan-600 mt-1 text-sm">
                            {{ $set->evidence_coverage }}%
                        </p>
                    </div>

                    {{-- Date & Time --}}
                    <div class="md:text-right">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">
                            {{ $set->created_at->format('d M Y · H:i') }}
                        </p>
                    </div>
                </a>
            @empty
                {{-- PREMIUM EMPTY STATE --}}
                <div class="p-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-cyan-50 border border-cyan-100 text-cyan-600 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-slate-900 uppercase tracking-wider">No Saved Comparison Sets</h3>
                    <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
                        You haven't generated or saved any multi-scenario Digital Twin comparisons yet.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('digital-twin.index') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-black uppercase tracking-widest transition-all shadow-md">
                            Run Digital Twin Analysis →
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $comparisonSets->links() }}
        </div>
    </div>
</x-app-layout>