<x-app-layout>
    @php
        $baseline = $comparisonSet->baseline_snapshot ?? [];
        $scenarios = $comparisonSet->scenarios_snapshot ?? [];
        $comparison = $comparisonSet->comparison_snapshot ?? [];
    @endphp

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6">

        {{-- Back Button --}}
        <a href="{{ route('digital-twin.comparisons.history') }}"
           class="inline-flex items-center gap-2 text-slate-400 hover:text-cyan-400 transition-all hover:-translate-x-1 mb-8 font-bold text-xs uppercase tracking-widest">
            ← Comparison History
        </a>

        {{-- SNAPSHOT HEADER CARD --}}
        <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-400">
                            Saved Multi-Scenario Snapshot
                        </p>
                        <h1 class="text-3xl sm:text-4xl font-black text-white capitalize tracking-wide mt-2">
                            {{ $comparisonSet->name }}
                        </h1>
                        <p class="text-xs text-slate-400 font-semibold mt-2">
                            Engine {{ $comparisonSet->engine_version }} · {{ $comparisonSet->created_at->format('d M Y · H:i') }}
                        </p>
                    </div>

                    <div class="sm:text-right">
                        <span class="inline-flex px-4 py-2 rounded-xl bg-indigo-500/10 text-indigo-300 border border-indigo-500/30 text-[10px] font-black uppercase tracking-widest">
                            {{ data_get($comparison, 'decision_status', 'Stored Decision') }}
                        </span>
                    </div>
                </div>

                {{-- Decision Detail Box --}}
                <div class="mt-6 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 space-y-3">
                    <p class="text-sm text-slate-300 leading-relaxed font-medium">
                        {{ data_get($comparison, 'decision_reason', 'Stored comparison snapshot.') }}
                    </p>
                    
                    <div class="pt-3 border-t border-slate-700/50 flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">Preferred Decision:</span>
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                            {{ data_get($comparison, 'preferred_option') === 'scenario'
                                ? data_get($comparison, 'recommended_scenario.name', 'Scenario')
                                : 'Keep Current Plan' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SCENARIOS COMPARISON TABLE --}}
        <div class="overflow-x-auto rounded-3xl border border-slate-800 bg-slate-900 shadow-xl mt-8">
            <table class="w-full min-w-[800px] text-left border-collapse">
                <thead class="bg-slate-950/60 border-b border-slate-800">
                    <tr>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Option</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Risk Index</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Condition</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Feasibility</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Margin</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Carbon</th>
                        <th class="p-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Evidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @php
                        $allOptions = array_merge(
                            [[
                                'name' => 'Current Plan',
                                'snapshot' => $baseline,
                            ]],
                            array_map(
                                fn ($scenario) => [
                                    'name' => $scenario['name'] ?? 'Scenario',
                                    'snapshot' => $scenario,
                                ],
                                $scenarios
                            )
                        );
                    @endphp

                    @foreach($allOptions as $option)
                        @php
                            $snapshot = $option['snapshot'];

                            $isDry = data_get(
                                $snapshot,
                                'analysis.quality_prediction.condition_model_type'
                            ) === 'storage_stability';

                            $condition = $isDry
                                ? data_get(
                                    $snapshot,
                                    'analysis.quality_prediction.storage_stability_assessment.status',
                                    'Telemetry required'
                                )
                                : (
                                    data_get(
                                        $snapshot,
                                        'analysis.quality_at_arrival'
                                    ) !== null
                                        ? data_get(
                                            $snapshot,
                                            'analysis.quality_at_arrival'
                                        ) . '/100'
                                        : 'Not estimated'
                                );

                            $preferred = data_get(
                                $comparison,
                                'preferred_option'
                            ) === 'current_plan'
                                ? $option['name'] === 'Current Plan'
                                : data_get(
                                    $comparison,
                                    'recommended_scenario.name'
                                ) === $option['name'];
                        @endphp

                        <tr class="transition-colors duration-150 {{ $preferred ? 'bg-emerald-500/5 hover:bg-emerald-500/10' : 'hover:bg-slate-800/40' }}">
                            {{-- Option Name --}}
                            <td class="p-5">
                                <p class="font-black text-white text-sm">
                                    {{ $option['name'] }}
                                </p>
                                @if($preferred)
                                    <span class="inline-block text-[9px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded mt-1">
                                        Preferred Choice
                                    </span>
                                @endif
                            </td>

                            {{-- Operational Risk --}}
                            <td class="p-5 text-sm font-black text-white">
                                {{ data_get($snapshot, 'analysis.risk_score', '—') }}<span class="text-xs text-slate-500 font-bold">/100</span>
                            </td>

                            {{-- Condition --}}
                            <td class="p-5 text-xs font-bold text-slate-300">
                                {{ $condition }}
                            </td>

                            {{-- Feasibility --}}
                            <td class="p-5 text-xs font-black text-cyan-400 uppercase tracking-wide">
                                {{ data_get($snapshot, 'route.freshness_feasibility', '—') }}
                            </td>

                            {{-- Margin --}}
                            <td class="p-5 text-xs font-bold text-slate-300">
                                @if(data_get($snapshot, 'route.transit_margin_hours') !== null)
                                    {{ number_format(data_get($snapshot, 'route.transit_margin_hours'), 1) }} <span class="text-slate-500">h</span>
                                @else
                                    <span class="text-slate-500">Unavailable</span>
                                @endif
                            </td>

                            {{-- Carbon --}}
                            <td class="p-5 text-xs font-bold text-slate-300">
                                @if(data_get($snapshot, 'carbon.estimated_kg') !== null)
                                    {{ number_format(data_get($snapshot, 'carbon.estimated_kg'), 2) }} <span class="text-slate-500">kg</span>
                                @else
                                    <span class="text-slate-500">Unavailable</span>
                                @endif
                            </td>

                            {{-- Evidence --}}
                            <td class="p-5 text-xs font-black text-cyan-400">
                                {{ data_get($snapshot, 'evidence.percent', '—') }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- COMPARISON PROVENANCE CARD --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-xl mt-8">
            <h2 class="text-lg font-black text-white uppercase tracking-wider mb-6">
                Comparison Provenance
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl bg-slate-800/80 border border-slate-700/60 p-5">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Engine Version</p>
                    <p class="text-base font-black text-white mt-1">
                        {{ $comparisonSet->engine_version }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-800/80 border border-slate-700/60 p-5">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Average Evidence Coverage</p>
                    <p class="text-base font-black text-cyan-400 mt-1">
                        {{ $comparisonSet->evidence_coverage }}%
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-800/80 border border-slate-700/60 p-5 md:col-span-2">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Decision Method</p>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Deterministic hierarchical comparison: <strong class="text-white">feasibility → operational risk → condition outcome → transit margin → estimated carbon</strong>. No arbitrary multi-scenario AI score is used.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>