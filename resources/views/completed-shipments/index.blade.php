<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase tracking-[0.18em]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Operational Archive
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mt-3">Completed Shipments</h1>
                <p class="text-sm text-slate-500 mt-2 max-w-2xl leading-relaxed">
                    Delivered shipments are removed from active decision workflows. Their final operational snapshot and historical analyses remain available for audit and review.
                </p>
            </div>

            <a href="{{ route('shipments.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 transition-colors">
                ← Active Shipments
            </a>
        </div>

        @foreach(['success' => 'emerald', 'warning' => 'amber', 'info' => 'cyan'] as $flash => $tone)
            @if(session($flash))
                <div class="mb-6 rounded-2xl border px-5 py-4 text-sm font-bold
                    {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : '' }}
                    {{ $tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-800' : '' }}
                    {{ $tone === 'cyan' ? 'border-cyan-200 bg-cyan-50 text-cyan-800' : '' }}">
                    {{ session($flash) }}
                </div>
            @endif
        @endforeach

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($shipments as $shipment)
                @php
                    $snapshot = $shipment->completion_snapshot ?? [];
                    $final = $snapshot['analysis'] ?? [];
                    $risk = $final['risk_score'] ?? null;
                    $readiness = $final['operational_readiness_score'] ?? null;
                    $condition = $final['condition_assessment']['overall_status']
                        ?? ($final['quality_prediction']['condition_assessment']['overall_status'] ?? null);
                    $riskLevel = $final['risk_severity'] ?? $final['risk_level'] ?? null;
                    $riskLabel = $riskLevel === 'Medium' ? 'Moderate' : $riskLevel;
                @endphp

                <article class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] uppercase font-black tracking-[0.2em] text-emerald-600">Delivered</p>
                            <h2 class="text-xl font-black text-slate-900 mt-2 capitalize">{{ $shipment->harvest?->commodity ?? 'Unknown commodity' }}</h2>
                            <p class="text-xs text-slate-500 mt-2">{{ $shipment->origin }} → {{ $shipment->destination }}</p>
                        </div>
                        <span class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-700">Completed</span>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">Delivered At</p>
                        <p class="text-sm font-black text-slate-800 mt-1">
                            {{ $shipment->delivered_at ? $shipment->delivered_at->format('d M Y · H:i') : 'Not recorded (legacy completion)' }}
                        </p>
                    </div>

                    @if(!empty($snapshot))
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div class="rounded-2xl border border-slate-100 p-4">
                                <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Final Risk</p>
                                <p class="text-lg font-black text-slate-900 mt-1">{{ $risk !== null ? $risk . '/100' : '—' }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">{{ $riskLabel ?? 'Unavailable' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 p-4">
                                <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Final Readiness</p>
                                <p class="text-lg font-black text-slate-900 mt-1">{{ $readiness !== null ? $readiness . '/100' : '—' }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">Final active-state snapshot</p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-2xl border border-slate-100 p-4">
                            <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Final Condition</p>
                            <p class="text-sm font-black text-slate-800 mt-1">{{ $condition ?? 'Evidence unavailable' }}</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-black text-amber-800">Legacy completed shipment</p>
                            <p class="text-[11px] text-amber-700 mt-1 leading-relaxed">A final immutable completion snapshot was not stored by the earlier application version.</p>
                        </div>
                    @endif

                    <a href="{{ route('completed-shipments.show', $shipment) }}"
                       class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-xs font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-colors">
                        View Completed Record
                    </a>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-xl">✓</div>
                    <h2 class="text-lg font-black text-slate-900 mt-4">No completed shipments yet</h2>
                    <p class="text-sm text-slate-500 mt-2">When an active shipment is marked Delivered, it moves here automatically.</p>
                </div>
            @endforelse
        </div>

        @if($shipments->hasPages())
            <div class="mt-8">{{ $shipments->links() }}</div>
        @endif
    </div>
</x-app-layout>
