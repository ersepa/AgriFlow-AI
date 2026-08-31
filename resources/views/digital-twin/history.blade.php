<x-app-layout>
    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6">
        
{{-- HEADER SECTION --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
            <div>
                <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-600">
                    Operational Digital Twin
                </p>
                {{-- Diubah ke text-slate-900 agar kelihatan jelas --}}
                <h1 class="text-3xl font-black text-slate-900 mt-2 tracking-wide">
                    Scenario History
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Stored snapshots remain tied to the engine version used when they were created.
                </p>
            </div>

            <a href="{{ route('digital-twin.index') }}"
               class="inline-flex items-center gap-2 text-slate-500 hover:text-cyan-600 transition-all hover:-translate-x-1 font-bold text-xs uppercase tracking-widest">
                ← Back to Digital Twin
            </a>
        </div>

        {{-- HISTORY CONTAINER --}}
        <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
            @forelse($scenarios as $scenario)
                <a href="{{ route('digital-twin.scenarios.show', $scenario) }}"
                   class="grid grid-cols-1 md:grid-cols-6 gap-4 p-6 border-b border-slate-800/80 last:border-0 hover:bg-slate-800/50 transition-colors duration-200 items-center group">
                    
                    {{-- Scenario Name & Commodity --}}
                    <div class="md:col-span-2">
                        <p class="font-black text-white text-base group-hover:text-cyan-400 transition-colors">
                            {{ $scenario->name }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1 font-medium">
                            #{{ $scenario->shipment_id }} · <span class="text-slate-300 capitalize font-bold">{{ $scenario->shipment?->harvest?->commodity ?? 'Unknown' }}</span>
                        </p>
                    </div>

                    {{-- Risk Score --}}
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Risk Index</p>
                        <p class="font-black text-white mt-1 text-sm">
                            {{ data_get($scenario->result_snapshot, 'analysis.risk_score', '—') }}<span class="text-xs text-slate-500 font-bold">/100</span>
                        </p>
                    </div>

                    {{-- Feasibility --}}
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Feasibility</p>
                        <p class="font-black text-cyan-400 mt-1 text-sm uppercase tracking-wide">
                            {{ data_get($scenario->result_snapshot, 'route.freshness_feasibility', '—') }}
                        </p>
                    </div>

                    {{-- Evidence Coverage --}}
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Evidence</p>
                        <p class="font-black text-cyan-400 mt-1 text-sm">
                            {{ $scenario->evidence_coverage }}%
                        </p>
                    </div>

                    {{-- Date & Preferred Status --}}
                    <div class="md:text-right">
                        @if($scenario->is_preferred)
                            <span class="inline-flex px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest">
                                Preferred
                            </span>
                        @endif
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider {{ $scenario->is_preferred ? 'mt-2' : '' }}">
                            {{ $scenario->created_at->format('d M Y · H:i') }}
                        </p>
                    </div>
                </a>
            @empty
                {{-- EMPTY STATE --}}
                <div class="p-16 text-center bg-slate-900">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 text-cyan-400 flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-white uppercase tracking-wider">No Saved Scenarios Yet</h3>
                    <p class="text-xs text-slate-400 mt-2 max-w-sm mx-auto leading-relaxed">
                        You haven't generated or saved any single Digital Twin scenarios yet.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('digital-twin.index') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-600/30">
                            Create New Scenario →
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $scenarios->links() }}
        </div>
    </div>
</x-app-layout>