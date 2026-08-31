<x-app-layout>
    @php
        $result = $scenario->result_snapshot ?? [];
        $baseline = $scenario->baseline_snapshot ?? [];
        $comparison = $scenario->comparison_snapshot ?? [];
        $dry =
            data_get(
                $result,
                'analysis.quality_prediction.condition_model_type'
            ) === 'storage_stability';
    @endphp

    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6">

        {{-- Back Button --}}
        <a href="{{ route('digital-twin.scenarios.history') }}"
           class="inline-flex items-center gap-2 text-slate-400 hover:text-cyan-400 transition-all hover:-translate-x-1 mb-8 font-bold text-xs uppercase tracking-widest">
            ← Scenario History
        </a>

        {{-- SNAPSHOT HEADER CARD --}}
        <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-400">
                    Saved Digital Twin Snapshot
                </p>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mt-2">
                    <div>
                        <h1 class="text-3xl font-black text-white capitalize tracking-wide">
                            {{ $scenario->name }}
                        </h1>
                        <p class="text-xs text-slate-400 font-semibold mt-2">
                            Engine {{ $scenario->engine_version }} · {{ $scenario->created_at->format('d M Y · H:i') }}
                        </p>
                    </div>

                    @if($scenario->is_preferred)
                        <span class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 text-xs font-black uppercase text-emerald-400 tracking-widest self-start">
                            Preferred
                        </span>
                    @endif
                </div>

                <p class="text-sm text-slate-300 leading-relaxed mt-5 font-medium">
                    {{ data_get($comparison, 'decision_reason', 'Stored scenario snapshot.') }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <span class="rounded-lg bg-indigo-500/10 border border-indigo-500/30 px-3 py-1 text-xs font-black uppercase tracking-widest text-indigo-300">
                        {{ data_get($comparison, 'decision_status', 'Stored Scenario') }}
                    </span>

                    <span class="text-xs font-bold text-slate-400">
                        Preferred decision:
                        <strong class="text-white font-black">
                            {{ data_get($comparison, 'preferred_option') === 'scenario'
                                ? $scenario->name
                                : 'Keep Current Plan' }}
                        </strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- METRICS GRID --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-xl">
                <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Risk Index</p>
                <p class="text-2xl font-black text-white mt-2">
                    {{ data_get($result, 'analysis.risk_score', '—') }}<span class="text-xs text-slate-500 font-bold">/100</span>
                </p>
            </div>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-xl">
                <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">
                    {{ $dry ? 'Storage Condition' : 'Arrival Quality' }}
                </p>
                <p class="text-lg font-black text-emerald-400 mt-2">
                    @if($dry)
                        {{ data_get($result, 'analysis.quality_prediction.storage_stability_assessment.status', 'Unavailable') }}
                    @else
                        {{ data_get($result, 'analysis.quality_at_arrival') !== null
                            ? data_get($result, 'analysis.quality_at_arrival') . '/100'
                            : 'Not estimated' }}
                    @endif
                </p>
            </div>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-xl">
                <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Feasibility</p>
                <p class="text-lg font-black text-cyan-400 uppercase tracking-wide mt-2">
                    {{ data_get($result, 'route.freshness_feasibility', '—') }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-xl">
                <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Evidence Coverage</p>
                <p class="text-2xl font-black text-cyan-400 mt-2">
                    {{ $scenario->evidence_coverage }}%
                </p>
                <p class="text-[10px] text-slate-500 font-bold mt-1">
                    Not model accuracy
                </p>
            </div>
        </div>

        {{-- SCENARIO INPUTS CARD --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 mt-6 shadow-xl">
            <h2 class="text-lg font-black text-white uppercase tracking-wider">
                Scenario Inputs
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5">
                @foreach(($scenario->input_snapshot ?? []) as $key => $value)
                    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/60 p-4">
                        <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                            {{ str_replace('_', ' ', $key) }}
                        </p>
                        <p class="text-sm font-bold text-white mt-1 break-words">
                            {{ is_array($value) ? json_encode($value) : ($value ?? '—') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SNAPSHOT PROVENANCE CARD --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 mt-6 shadow-xl">
            <h2 class="text-lg font-black text-white uppercase tracking-wider mb-4">
                Snapshot Provenance
            </h2>
            <div class="text-xs text-slate-300 leading-relaxed space-y-2">
                <p><strong class="text-slate-400 uppercase text-[10px] font-black tracking-widest mr-2">Engine:</strong> {{ data_get($result, 'provenance.engine', $scenario->engine_version) }}</p>
                <p><strong class="text-slate-400 uppercase text-[10px] font-black tracking-widest mr-2">Commodity source:</strong> {{ data_get($result, 'provenance.commodity_profile.source_name', 'Unavailable') }}</p>
                <p><strong class="text-slate-400 uppercase text-[10px] font-black tracking-widest mr-2">Route Provider:</strong> {{ data_get($result, 'provenance.route_provider', 'Unavailable') }}</p>
                <p><strong class="text-slate-400 uppercase text-[10px] font-black tracking-widest mr-2">Calculation:</strong> deterministic decision support; not a spoilage probability or model-accuracy claim.</p>
            </div>
        </div>

        {{-- ACTION FORM --}}
        @unless($scenario->is_preferred)
            <form method="POST"
                  action="{{ route('digital-twin.scenarios.prefer', $scenario) }}"
                  class="mt-6">
                @csrf
                <button class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-4 text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-600/30">
                    Mark as Operator-Preferred Scenario
                </button>
                <p class="text-xs text-slate-500 text-center mt-3">
                    This records an operator preference only. It does not override the Step 6.1 decision or modify the recorded shipment.
                </p>
            </form>
        @endunless
    </div>
</x-app-layout>