<x-app-layout>
    <script src="https://unpkg.com/lucide@latest"></script>

    @php
        $result = $scenario->result_snapshot ?? [];
        $baseline = $scenario->baseline_snapshot ?? [];
        $comparison = $scenario->comparison_snapshot ?? [];
        $inputs = $scenario->input_snapshot ?? [];

        $dry = data_get($result, 'analysis.quality_prediction.condition_model_type') === 'storage_stability';
        $riskScore = data_get($result, 'analysis.risk_score');
        $arrivalQuality = data_get($result, 'analysis.quality_at_arrival');
        $storageStatus = data_get($result, 'analysis.quality_prediction.storage_stability_assessment.status');
        $feasibilityRaw = data_get($result, 'route.freshness_feasibility', 'Unavailable');

        $feasibilityLabel = match ($feasibilityRaw) {
            'Safe' => 'Suitable Delivery Conditions',
            'Tight' => 'Delivery Time Is Limited',
            'Breach' => 'Exceeds Safe Limit',
            default => $feasibilityRaw,
        };
        $feasibilityColor = match ($feasibilityRaw) {
            'Safe' => 'text-emerald-600',
            'Tight' => 'text-amber-600',
            'Breach' => 'text-rose-600',
            default => 'text-slate-600',
        };

        $riskColor = $riskScore === null
            ? 'text-slate-700'
            : ($riskScore >= 70 ? 'text-rose-600' : ($riskScore >= 40 ? 'text-amber-600' : 'text-emerald-600'));

        $inputLabels = [
            'vehicle' => ['label' => 'Delivery Vehicle', 'icon' => 'truck'],
            'vehicle_plan' => ['label' => 'Delivery Vehicle', 'icon' => 'truck'],
            'delay_hours' => ['label' => 'Extra Delivery Delay', 'icon' => 'clock'],
            'additional_delay' => ['label' => 'Extra Delivery Delay', 'icon' => 'clock'],
            'temperature_c' => ['label' => 'Storage Temperature', 'icon' => 'thermometer'],
            'relative_humidity_percent' => ['label' => 'Storage Humidity', 'icon' => 'droplets'],
            'moisture_percent' => ['label' => 'Product Moisture', 'icon' => 'droplets'],
            'route_rank' => ['label' => 'Selected Route Option', 'icon' => 'route'],
            'storage_horizon' => ['label' => 'Storage Horizon', 'icon' => 'calendar'],
            'name' => ['label' => 'Scenario Name', 'icon' => 'tag'],
        ];

        $formatInputValue = function ($key, $value) {
            if ($value === null || $value === '') {
                return 'Not specified';
            }
            if (is_array($value)) {
                return collect($value)->map(fn ($v, $k) => is_scalar($v) ? "{$k}: {$v}" : '—')->implode(', ');
            }
            return match ($key) {
                'vehicle', 'vehicle_plan' => ucwords(str_replace('_', ' ', (string) $value)),
                'delay_hours', 'additional_delay' => $value . ' hour(s)',
                'temperature_c' => $value . ' °C',
                'relative_humidity_percent', 'moisture_percent' => $value . '%',
                'storage_horizon' => ucwords(str_replace('_', ' ', (string) $value)),
                default => (string) $value,
            };
        };
    @endphp

    <div class="min-h-screen bg-slate-50/80 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Back Link --}}
            <a href="{{ route('digital-twin.scenarios.history') }}"
               class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Scenario History</span>
            </a>

            {{-- A. SCENARIO OVERVIEW --}}
            <section class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 via-teal-500 to-emerald-500"></div>

                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="cpu" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Simulation Details</p>
                            <h1 class="text-2xl font-bold text-slate-900 mt-0.5 capitalize">
                                {{ $scenario->name }}
                            </h1>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>Saved on {{ $scenario->created_at?->format('d M Y · H:i') ?? '—' }}</span>
                            </p>
                        </div>
                    </div>

                    @if($scenario->is_preferred)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold self-start">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                            <span>Preferred Scenario</span>
                        </span>
                    @endif
                </div>

                <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-2.5 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-xs font-bold">
                            {{ data_get($comparison, 'decision_status', 'Saved Scenario') }}
                        </span>
                        <span class="text-xs text-slate-600">
                            Recommended choice:
                            <strong class="text-slate-900">
                                {{ data_get($comparison, 'preferred_option') === 'scenario' ? $scenario->name : 'Keep Current Plan' }}
                            </strong>
                        </span>
                    </div>
                    <p class="text-sm text-slate-700 leading-relaxed">
                        {{ data_get($comparison, 'decision_reason', 'This saved snapshot captures the simulated delivery outcome for your shipment.') }}
                    </p>
                </div>
            </section>

            {{-- B. RESULT SUMMARY KPI CARDS --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-semibold">Delivery Risk</span>
                        <i data-lucide="shield-alert" class="w-4 h-4 {{ $riskColor }}"></i>
                    </div>
                    <p class="text-2xl font-bold {{ $riskColor }}">
                        {{ $riskScore ?? '—' }}<span class="text-xs font-medium text-slate-400">/100</span>
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1.5">Lower scores indicate safer transit.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-semibold">{{ $dry ? 'Storage Condition' : 'Estimated Product Quality' }}</span>
                        <i data-lucide="leaf" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">
                        @if($dry)
                            <span class="text-lg">{{ $storageStatus ?? 'Unavailable' }}</span>
                        @else
                            {{ $arrivalQuality !== null ? $arrivalQuality . '/100' : 'Not estimated' }}
                        @endif
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1.5">Expected harvest condition on arrival.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-semibold">Delivery Condition</span>
                        <i data-lucide="clock" class="w-4 h-4 {{ $feasibilityColor }}"></i>
                    </div>
                    <p class="text-sm font-bold {{ $feasibilityColor }} mt-1">
                        {{ $feasibilityLabel }}
                    </p>
                    <p class="text-[11px] text-slate-500 mt-2">Based on safe transit window.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-semibold">Data Coverage</span>
                        <i data-lucide="database" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">
                        {{ $scenario->evidence_coverage ?? 0 }}%
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Supporting data available (not model accuracy).
                    </p>
                </div>
            </section>

            {{-- C. CONDITIONS USED IN THIS SIMULATION --}}
            <section class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-indigo-600"></i>
                    <h2 class="text-base font-bold text-slate-900">Conditions Used in This Simulation</h2>
                </div>

                @if(!empty($inputs))
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        @foreach($inputs as $key => $value)
                            @php
                                $meta = $inputLabels[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'icon' => 'info'];
                            @endphp
                            <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-4 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-indigo-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold text-slate-500">{{ $meta['label'] }}</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5 break-words">
                                        {{ $formatInputValue($key, $value) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">No input conditions were recorded for this snapshot.</p>
                @endif
            </section>

            {{-- D. ABOUT THIS SIMULATION (EXPANDABLE PROVENANCE) --}}
            <section class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden" x-data="{ open: false }">
                <button type="button"
                        @click="open = !open"
                        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-slate-500"></i>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">About This Simulation</h2>
                            <p class="text-xs text-slate-500">Engine version, commodity data source, and routing provider details</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak class="px-6 pb-5 pt-2 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] font-semibold text-slate-500">Simulation Engine</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ data_get($result, 'provenance.engine', $scenario->engine_version ?? '—') }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] font-semibold text-slate-500">Commodity Data Source</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ data_get($result, 'provenance.commodity_profile.source_name', 'Unavailable') }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] font-semibold text-slate-500">Routing Provider</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ data_get($result, 'provenance.route_provider', 'Unavailable') }}</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 pt-2">
                        <strong>Note:</strong> Deterministic decision support; not a spoilage probability or model-accuracy claim.
                    </p>
                </div>
            </section>

            {{-- E. PREFERRED SCENARIO ACTION --}}
            @unless($scenario->is_preferred)
                <form method="POST"
                      action="{{ route('digital-twin.scenarios.prefer', $scenario) }}"
                      class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 text-center space-y-3">
                    @csrf
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-sm transition-colors">
                        <i data-lucide="bookmark-check" class="w-4 h-4"></i>
                        <span>Choose This as Preferred Scenario</span>
                    </button>
                    <p class="text-xs text-slate-500 max-w-xl mx-auto">
                        Recording this preference does not automatically change the active shipment or override the system's calculated recommendation.
                    </p>
                </form>
            @endunless

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</x-app-layout>