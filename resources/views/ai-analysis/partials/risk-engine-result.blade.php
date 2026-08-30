@php
    $riskAssessment = session('risk_assessment', []);
@endphp

@if(!empty($riskAssessment))
    <div class="mb-8 bg-slate-800/60 border border-slate-700/80 rounded-3xl p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 pb-5 border-b border-slate-700">
            <div>
                <p class="text-[10px] uppercase tracking-[0.25em] text-indigo-400 font-black">
                    Operational Risk Engine · Step 4
                </p>

                <h2 class="text-2xl font-black text-white mt-1">
                    Post-Harvest Loss Risk Assessment
                </h2>

                <p class="text-slate-400 text-xs mt-2 max-w-2xl leading-relaxed">
                    Deterministic operational risk index driven primarily by arrival quality,
                    remaining shelf life, transit margin, and temperature exposure. This is
                    not a statistical spoilage probability.
                </p>
            </div>

            <div class="sm:text-right">
                <div class="flex sm:justify-end items-end gap-2">
                    <span class="text-5xl font-black text-white leading-none">
                        {{ $riskAssessment['risk_score'] ?? 0 }}
                    </span>
                    <span class="text-sm font-black text-slate-500 mb-1">/100</span>
                </div>

                <span class="inline-flex mt-3 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                    {{ ($riskAssessment['risk_severity'] ?? '') === 'Critical' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : '' }}
                    {{ ($riskAssessment['risk_severity'] ?? '') === 'High' ? 'bg-orange-500/20 text-orange-300 border-orange-500/30' : '' }}
                    {{ ($riskAssessment['risk_severity'] ?? '') === 'Moderate' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : '' }}
                    {{ ($riskAssessment['risk_severity'] ?? '') === 'Low' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : '' }}
                ">
                    {{ $riskAssessment['risk_severity'] ?? 'Unknown' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
            <div class="bg-slate-900/50 rounded-2xl border border-slate-700 p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Urgency</p>
                <p class="text-xl font-black text-white mt-2">{{ $riskAssessment['urgency_level'] ?? 'Unknown' }}</p>
            </div>

            <div class="bg-slate-900/50 rounded-2xl border border-slate-700 p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Intervention</p>
                <p class="text-xl font-black mt-2 {{ ($riskAssessment['intervention_required'] ?? false) ? 'text-amber-300' : 'text-emerald-300' }}">
                    {{ $riskAssessment['intervention_status'] ?? (($riskAssessment['intervention_required'] ?? false) ? 'Required' : 'Routine Monitoring') }}
                </p>
            </div>

            <div class="bg-slate-900/50 rounded-2xl border border-slate-700 p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Action Window</p>
                <p class="text-xl font-black text-cyan-300 mt-2">{{ $riskAssessment['dispatch_deadline'] ?? 'Flexible' }}</p>
            </div>
        </div>

        <div class="mt-5 bg-indigo-500/5 border border-indigo-500/20 rounded-2xl p-5">
            <p class="text-[10px] uppercase font-black text-indigo-300 tracking-widest">Why this urgency?</p>
            <p class="text-sm text-slate-300 leading-relaxed mt-2">{{ $riskAssessment['urgency_reason'] ?? 'No urgency reason available.' }}</p>
        </div>

        @if($riskAssessment['critical_override_applied'] ?? false)
            <div class="mt-5 bg-rose-500/10 border border-rose-500/30 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-rose-300 tracking-widest">Critical Constraint Override</p>
                <p class="text-sm text-rose-100 mt-2 leading-relaxed">{{ $riskAssessment['critical_override_reason'] }}</p>
            </div>
        @endif

        <details class="mt-5 group">
            <summary class="cursor-pointer list-none flex items-center justify-between gap-4 bg-slate-900/40 border border-slate-700 rounded-xl px-5 py-4">
                <span class="text-xs font-black text-slate-300 uppercase tracking-widest">Model Notes</span>
                <span class="text-indigo-300 group-open:rotate-45 transition-transform">+</span>
            </summary>

            <ul class="mt-4 space-y-2 px-5">
                @foreach(($riskAssessment['limitations'] ?? []) as $limitation)
                    <li class="text-xs text-slate-400 leading-relaxed">• {{ $limitation }}</li>
                @endforeach
            </ul>
        </details>
    </div>
@endif
