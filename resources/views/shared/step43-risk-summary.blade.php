@php
    $riskData = $step43Analysis['risk_assessment'] ?? [];
    $riskScore = $step43Analysis['risk_score'] ?? null;
    $severity = $step43Analysis['risk_severity'] ?? ($riskData['risk_severity'] ?? 'Unknown');
    $urgency = $step43Analysis['urgency_level'] ?? ($riskData['urgency_level'] ?? 'Unknown');
    $interventionStatus = $riskData['intervention_status'] ?? ($step43Analysis['intervention_required'] ?? false ? 'Required' : 'Monitor');
    $actionWindow = $step43Analysis['dispatch_deadline'] ?? ($riskData['dispatch_deadline'] ?? 'Flexible');
    $drivers = $riskData['top_drivers'] ?? [];
@endphp

@if(!empty($riskData))
    <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 border-b border-slate-800 pb-5 mb-6">
            <div>
                <p class="text-[10px] uppercase font-black text-indigo-400 tracking-[0.22em]">
                    Operational Risk Engine · Step 4
                </p>
                <h2 class="text-xl font-black text-white mt-2">Post-Harvest Loss Risk Assessment</h2>
                <p class="text-xs text-slate-400 mt-2 max-w-2xl leading-relaxed">
                    Deterministic operational risk index. This is not a statistical spoilage probability.
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-4xl font-black text-white">
                    {{ $riskScore !== null ? number_format($riskScore, 0) : '—' }}<span class="text-sm text-slate-500">/100</span>
                </p>
                <p class="text-xs font-black uppercase tracking-widest text-indigo-300 mt-2">{{ $severity }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Urgency</p>
                <p class="text-lg font-black text-white mt-2">{{ $urgency }}</p>
            </div>
            <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Intervention</p>
                <p class="text-lg font-black text-white mt-2">{{ $interventionStatus }}</p>
            </div>
            <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-5">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Action Window</p>
                <p class="text-lg font-black text-cyan-400 mt-2">{{ $actionWindow }}</p>
            </div>
        </div>

        @if(!empty($drivers))
            <div class="mt-6">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest mb-3">Top Risk Drivers</p>
                <div class="space-y-3">
                    @foreach(array_slice($drivers, 0, 3) as $driver)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-3">
                            <div>
                                <p class="text-sm font-bold text-slate-200">{{ $driver['title'] ?? 'Risk driver' }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $driver['reason'] ?? '' }}</p>
                            </div>
                            <div class="sm:text-right shrink-0">
                                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">Contribution</p>
                                <p class="text-sm font-black text-indigo-300 mt-1">{{ number_format($driver['contribution'] ?? 0, 1) }} pts</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
