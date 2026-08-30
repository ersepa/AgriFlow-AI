@php
    $recommendationPlan = session('recommendation_plan', []);
    $recommendedActions = $recommendationPlan['actions'] ?? session('recommended_actions', []);
@endphp

@if(!empty($recommendedActions))
    <div class="pt-6 border-t border-slate-700/80">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
            <div>
                <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.22em]">
                    Intervention Recommendation Engine · Step 4.3
                </p>
                <h3 class="text-xl font-black text-white mt-1">
                    Recommended Operational Actions
                </h3>
                <p class="text-xs text-slate-400 mt-2 max-w-2xl leading-relaxed">
                    Deterministic actions generated from freshness condition, operational risk,
                    urgency, transit margin, and available commodity reference guidance.
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    Overall Action Window
                </p>
                <p class="text-sm font-black text-cyan-300 mt-1">
                    {{ $recommendationPlan['action_window'] ?? session('dispatch_deadline', 'Flexible') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach($recommendedActions as $index => $action)
                <div class="bg-slate-800/80 border {{ $index === 0 ? 'border-indigo-500/40' : 'border-slate-700' }} rounded-2xl p-5 relative overflow-hidden">
                    @if($index === 0)
                        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-500"></div>
                    @endif

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl {{ $index === 0 ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-700/70 text-slate-300' }} text-xs font-black shrink-0">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="text-[10px] uppercase tracking-widest font-black text-slate-400">
                            {{ $action['label'] ?? 'Action' }}
                        </span>
                    </div>

                    <h4 class="text-sm font-black text-white mt-4 leading-relaxed">
                        {{ $action['action'] ?? 'Review shipment' }}
                    </h4>

                    <p class="text-xs text-slate-400 leading-relaxed mt-3">
                        {{ $action['reason'] ?? '' }}
                    </p>

                    @if(!empty($action['window']))
                        <div class="mt-4 pt-3 border-t border-slate-700/70 flex items-center justify-between gap-3">
                            <span class="text-[9px] uppercase font-black tracking-widest text-slate-500">
                                Timing
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-cyan-400 text-right">
                                {{ $action['window'] }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-5">
            <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-indigo-300 tracking-widest">
                    Decision Rationale
                </p>
                <p class="text-sm text-slate-300 leading-relaxed mt-2">
                    {{ $recommendationPlan['decision_rationale'] ?? session('decision_rationale', 'Continue monitoring current operational conditions.') }}
                </p>
            </div>

            <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-emerald-300 tracking-widest">
                    Expected Operational Outcome
                </p>
                <p class="text-sm text-slate-300 leading-relaxed mt-2">
                    {{ $recommendationPlan['expected_outcome'] ?? session('expected_outcome', 'Continue monitoring against the current operational constraints.') }}
                </p>
            </div>
        </div>

        <details class="mt-5 group">
            <summary class="cursor-pointer list-none flex items-center justify-between gap-4 bg-slate-800/50 border border-slate-700 rounded-xl px-5 py-4">
                <span class="text-xs font-black text-slate-300 uppercase tracking-widest">
                    Commodity Handling Guidance
                </span>
                <span class="text-indigo-300 group-open:rotate-45 transition-transform">+</span>
            </summary>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4 px-1">
                <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Transport</p>
                    <p class="text-sm font-bold text-slate-200 mt-2">
                        {{ $recommendationPlan['recommended_vehicle'] ?? 'Use appropriate transport conditions.' }}
                    </p>
                </div>

                <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Temperature</p>
                    <p class="text-sm font-bold text-slate-200 mt-2">
                        {{ $recommendationPlan['recommended_temperature_range'] ?? 'Not available' }}
                    </p>
                </div>

                <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Humidity</p>
                    <p class="text-sm font-bold text-slate-200 mt-2">
                        {{ $recommendationPlan['recommended_humidity_range'] ?? 'Not available' }}
                    </p>
                </div>

                <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Chilling Threshold</p>
                    <p class="text-sm font-bold text-slate-200 mt-2">
                        {{ $recommendationPlan['chilling_threshold'] ?? 'No validated threshold stored' }}
                    </p>
                </div>
            </div>

            <div class="mt-4 bg-slate-950/30 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Profile</p>
                    <p class="text-xs text-slate-300 mt-1">
                        {{ $recommendationPlan['commodity_profile_name'] ?? 'Commodity profile unavailable' }}
                    </p>
                </div>

                @if(!empty($recommendationPlan['reference_source_url']))
                    <a href="{{ $recommendationPlan['reference_source_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="text-xs font-black text-cyan-400 hover:text-cyan-300 uppercase tracking-widest">
                        {{ $recommendationPlan['reference_source_name'] ?? 'View reference source' }} →
                    </a>
                @endif
            </div>
        </details>
    </div>
@endif
