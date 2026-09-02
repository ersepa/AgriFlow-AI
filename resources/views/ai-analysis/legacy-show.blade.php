<x-app-layout>
    @php
        $raw = trim((string) $analysisRecord->recommendations);
        $recommendations = [];
        $explanation = '';
        $conclusion = '';

        if ($raw !== '') {
            $after = preg_replace('/^.*?Recommendations:\s*/s', '', $raw, 1);
            $parts = preg_split('/\R*Explanation:\s*/', $after ?? $raw, 2) ?: [];
            $recommendationText = trim($parts[0] ?? '');
            $tail = trim($parts[1] ?? '');
            $tailParts = preg_split('/\R*Conclusion:\s*/', $tail, 2) ?: [];
            $explanation = trim($tailParts[0] ?? '');
            $conclusion = trim($tailParts[1] ?? '');

            $recommendations = array_values(array_filter(array_map(
                static fn (string $line): string => trim(preg_replace('/^\s*-\s*/', '', $line)),
                preg_split('/\R+/', $recommendationText) ?: []
            )));

            if (empty($recommendations) && !str_contains($raw, 'Recommendations:')) {
                $recommendations = [$raw];
            }
        }

        $riskLabel = $analysisRecord->risk_level === 'Medium'
            ? 'Moderate'
            : $analysisRecord->risk_level;
    @endphp

    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6">
        <a href="{{ route('ai-analysis.history') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors text-xs font-black uppercase tracking-widest mb-7">
            ← Back to analysis history
        </a>

        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 sm:p-8 mb-6">
            <p class="text-[10px] uppercase font-black tracking-[0.22em] text-amber-700">Legacy Analysis Record</p>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">Historical snapshot metadata was not stored by this earlier application version.</h1>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed max-w-3xl">
                AgriFlow shows only the values that were actually persisted at the time. It does not recompute this historical record using the shipment's current condition, route, or decision model.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 text-white">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 pb-5 border-b border-slate-800">
                    <div>
                        <p class="text-[10px] uppercase font-black text-indigo-400 tracking-[0.22em]">Persisted Historical Analysis</p>
                        <h2 class="text-3xl font-black mt-2 capitalize">{{ $shipment->harvest?->commodity ?? 'Unknown commodity' }}</h2>
                        <p class="text-sm text-slate-400 mt-2">{{ $shipment->origin }} → {{ $shipment->destination }}</p>
                    </div>
                    <div class="sm:text-right">
                        <span class="inline-flex rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-black uppercase tracking-widest">
                            {{ $riskLabel }} Risk
                        </span>
                        <p class="text-xs text-slate-500 font-bold mt-3">{{ $analysisRecord->created_at?->format('d M Y · H:i') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-500">Stored Operational Risk Index*</p>
                        <p class="text-2xl font-black mt-2">{{ $analysisRecord->waste_probability }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-500">Stored Decision Score*</p>
                        <p class="text-2xl font-black mt-2">{{ $analysisRecord->sustainability_score }}</p>
                    </div>
                </div>

                @if(!empty($recommendations))
                    <div class="mt-6">
                        <p class="text-[10px] uppercase font-black tracking-widest text-indigo-400">Persisted Recommendations</p>
                        <ul class="mt-3 space-y-2">
                            @foreach($recommendations as $item)
                                <li class="text-sm text-slate-300 flex gap-2"><span class="text-indigo-400">•</span><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($explanation)
                    <div class="mt-6 pt-5 border-t border-slate-800">
                        <p class="text-[10px] uppercase font-black tracking-widest text-cyan-400">Persisted Explanation</p>
                        <p class="text-sm text-slate-300 mt-2 leading-relaxed">{{ $explanation }}</p>
                    </div>
                @endif

                @if($conclusion)
                    <div class="mt-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                        <p class="text-[10px] uppercase font-black tracking-widest text-emerald-400">Persisted Outcome</p>
                        <p class="text-sm text-slate-300 mt-2 leading-relaxed">{{ $conclusion }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">Shipment Current Lifecycle</p>
                    <p class="text-lg font-black text-slate-900 mt-2">{{ $shipment->status }}</p>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        This label is current shipment metadata. It is intentionally not used to recalculate the historical analysis above.
                    </p>
                </div>

                @if($shipment->isDelivered())
                    <a href="{{ route('completed-shipments.show', $shipment) }}"
                       class="block bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl px-5 py-4 text-center text-xs font-black uppercase tracking-widest transition-colors">
                        View Completed Shipment
                    </a>
                @endif
            </div>
        </div>

        <p class="text-[11px] text-slate-500 mt-6 leading-relaxed">
            * Legacy database column names are retained for compatibility. Their meaning may reflect the application logic used when the record was created.
        </p>
    </div>
</x-app-layout>
