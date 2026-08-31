@php
    $plan = $step43Analysis['recommendation_plan'] ?? [];
    $actions = $plan['actions'] ?? [];
@endphp

@if(!empty($actions))
    <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-800 pb-5 mb-6">
            <div>
                <p class="text-[10px] uppercase font-black text-indigo-400 tracking-[0.22em]">Intervention Recommendation Engine</p>
                <h2 class="text-xl font-black text-white mt-2">Recommended Operational Actions</h2>
            </div>
            <div class="sm:text-right">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Overall Action Window</p>
                <p class="text-sm font-black text-cyan-400 mt-1">{{ $plan['action_window'] ?? 'Flexible' }}</p>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($actions as $index => $action)
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-5">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-500/15 text-indigo-300 text-xs font-black shrink-0">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">{{ $action['label'] ?? 'Action' }}</p>
                    </div>
                    <h3 class="text-sm font-black text-white mt-4">{{ $action['action'] ?? 'Review shipment' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mt-2">{{ $action['reason'] ?? '' }}</p>
                    @if(!empty($action['window']))
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400 mt-3">Timing: {{ $action['window'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-5">
            <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-indigo-300 tracking-widest">Decision Rationale</p>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">{{ $plan['decision_rationale'] ?? 'Continue monitoring operational conditions.' }}</p>
            </div>
            <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-emerald-300 tracking-widest">Expected Operational Outcome</p>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">{{ $plan['expected_outcome'] ?? 'Preserve the current operational freshness window.' }}</p>
            </div>
        </div>

        <details class="mt-5 group">
            <summary class="cursor-pointer list-none flex items-center justify-between gap-4 bg-slate-800/50 border border-slate-700 rounded-xl px-5 py-4">
                <span class="text-xs font-black text-slate-300 uppercase tracking-widest">
                    Commodity Handling Guidance
                </span>
                <span class="text-indigo-300 group-open:rotate-45 transition-transform">+</span>
            </summary>

            @if(($plan['quality_model_type'] ?? null) === 'storage_stability')
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4 px-1">
                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                            Storage Model
                        </p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            Dry Commodity Stability
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                            Moisture Reference
                        </p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            @if(($plan['safe_moisture_short_term_max_percent'] ?? null) !== null)
                                ≤ {{ number_format($plan['safe_moisture_short_term_max_percent'], 1) }}%
                                @if(
                                    ($plan['safe_moisture_long_term_max_percent'] ?? null) !== null
                                    && $plan['safe_moisture_long_term_max_percent']
                                        != $plan['safe_moisture_short_term_max_percent']
                                )
                                    · long-term ≤ {{ number_format($plan['safe_moisture_long_term_max_percent'], 1) }}%
                                @endif
                            @else
                                Not available
                            @endif
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                            Relative Humidity Guidance
                        </p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            @if(($plan['safe_relative_humidity_max_percent'] ?? null) !== null)
                                ≤ {{ number_format($plan['safe_relative_humidity_max_percent'], 0) }}% RH
                            @else
                                Not available
                            @endif
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                            Evidence Status
                        </p>
                        <p class="text-sm font-bold text-amber-300 mt-2">
                            {{ $plan['storage_evidence_status'] ?? 'Condition telemetry not available' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 bg-slate-950/30 border border-slate-800 rounded-xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                        Transport Guidance
                    </p>
                    <p class="text-sm font-bold text-slate-200 mt-2">
                        {{ $plan['recommended_vehicle'] ?? 'Keep cargo dry and protected from moisture ingress.' }}
                    </p>

                    @if(!empty($plan['storage_science_note']))
                        <p class="text-xs text-slate-500 leading-relaxed mt-3">
                            {{ $plan['storage_science_note'] }}
                        </p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4 px-1">
                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Transport</p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            {{ $plan['recommended_vehicle'] ?? 'Use appropriate transport conditions.' }}
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Temperature</p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            {{ $plan['recommended_temperature_range'] ?? 'Not available' }}
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Humidity</p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            {{ $plan['recommended_humidity_range'] ?? 'Not available' }}
                        </p>
                    </div>

                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Chilling Threshold</p>
                        <p class="text-sm font-bold text-slate-200 mt-2">
                            {{ $plan['chilling_threshold'] ?? 'No validated threshold stored' }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="mt-4 bg-slate-950/30 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Profile</p>
                    <p class="text-xs text-slate-300 mt-1">
                        {{ $plan['commodity_profile_name'] ?? 'Commodity profile unavailable' }}
                    </p>
                </div>

                @if(!empty($plan['reference_source_url']))
                    <a href="{{ $plan['reference_source_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="text-xs font-black text-cyan-400 hover:text-cyan-300 uppercase tracking-widest">
                        {{ $plan['reference_source_name'] ?? 'View reference source' }} →
                    </a>
                @endif
            </div>
        </details>
    </div>
@endif
