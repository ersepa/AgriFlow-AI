@php
    $condition = $analysis['condition_assessment']
        ?? ($analysis['quality_prediction']['condition_assessment'] ?? []);
    $profile = $analysis['commodity_profile'] ?? [];
    $modelType = $condition['condition_model_type']
        ?? ($profile['quality_model_type'] ?? 'shelf_life_quality');
    $isDry = $modelType === 'storage_stability';
    $overallStatus = $condition['overall_status'] ?? 'Condition evidence unavailable';
    $evidenceStatus = $condition['evidence_status'] ?? 'Unavailable';
    $sourceName = $condition['source_name'] ?? ($profile['source_name'] ?? null);
    $sourceUrl = $condition['source_url'] ?? ($profile['source_url'] ?? null);
    $recordedAt = $shipment->condition_recorded_at;
    $statusTone = str_contains(strtolower($overallStatus), 'outside')
        ? 'rose'
        : (str_contains(strtolower($overallStatus), 'unavailable') ? 'slate' : 'emerald');

    $temperature = $condition['temperature'] ?? [];
    $humidity = $condition['relative_humidity'] ?? [];
    $moisture = $condition['moisture'] ?? [];

    $temperatureValue = $temperature['temperature_c']
        ?? $shipment->recorded_temperature_c;
    $humidityValue = $humidity['relative_humidity_percent']
        ?? $humidity['value_percent']
        ?? $shipment->recorded_relative_humidity_percent;
    $moistureValue = $moisture['value_percent']
        ?? $shipment->recorded_moisture_percent;
@endphp

<div class="bg-slate-900 text-white p-6 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-lg">🌡️</span>
                    <h3 class="text-xs font-black text-cyan-400 uppercase tracking-widest">
                        Condition Intelligence
                    </h3>
                </div>
                <p class="mt-2 text-sm font-black text-white">
                    {{ $isDry ? 'Storage-Stability Condition' : 'Cold-Chain Condition' }}
                </p>
            </div>

            <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-widest
                {{ $statusTone === 'rose'
                    ? 'border-rose-500/30 bg-rose-500/10 text-rose-300'
                    : ($statusTone === 'emerald'
                        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                        : 'border-slate-700 bg-slate-800 text-slate-300') }}">
                {{ $overallStatus }}
            </span>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            @if($isDry)
                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Cargo Moisture</p>
                    <p class="mt-2 text-lg font-black text-white">
                        {{ $moistureValue !== null ? number_format((float) $moistureValue, 1) . '%' : '—' }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">
                        Reference limit:
                        {{ isset($moisture['reference_limit_percent'])
                            ? '≤ ' . number_format((float) $moisture['reference_limit_percent'], 1) . '%'
                            : 'Unavailable' }}
                    </p>
                </div>
            @else
                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Cargo Temperature</p>
                    <p class="mt-2 text-lg font-black text-white">
                        {{ $temperatureValue !== null ? number_format((float) $temperatureValue, 1) . '°C' : '—' }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">
                        Reference:
                        @if(isset($temperature['reference_min_c'], $temperature['reference_max_c']))
                            {{ number_format((float) $temperature['reference_min_c'], 1) }}–{{ number_format((float) $temperature['reference_max_c'], 1) }}°C
                        @else
                            Unavailable
                        @endif
                    </p>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Relative Humidity</p>
                <p class="mt-2 text-lg font-black text-white">
                    {{ $humidityValue !== null ? number_format((float) $humidityValue, 1) . '% RH' : '—' }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    @if($isDry)
                        Reference limit:
                        {{ isset($humidity['reference_limit_percent'])
                            ? '≤ ' . number_format((float) $humidity['reference_limit_percent'], 1) . '% RH'
                            : 'Unavailable' }}
                    @else
                        Reference:
                        @if(isset($humidity['reference_min_percent'], $humidity['reference_max_percent']))
                            {{ number_format((float) $humidity['reference_min_percent'], 0) }}–{{ number_format((float) $humidity['reference_max_percent'], 0) }}% RH
                        @else
                            Unavailable
                        @endif
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/30 p-4">
            <div class="flex items-center justify-between gap-4">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Evidence</span>
                <span class="text-[11px] font-black text-cyan-300">{{ $evidenceStatus }}</span>
            </div>

            <p class="mt-2 text-xs leading-relaxed text-slate-300">
                {{ $condition['primary_driver'] ?? 'No recorded shipment condition is available. AgriFlow keeps this as an evidence gap.' }}
            </p>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-800 space-y-2 text-[11px] text-slate-400">
            <div class="flex justify-between gap-4">
                <span>Recorded Source</span>
                <span class="font-bold text-slate-300">
                    {{ $shipment->condition_source === 'manual_entry' ? 'Manual Entry' : ($shipment->condition_source ?? 'Not recorded') }}
                </span>
            </div>
            <div class="flex justify-between gap-4">
                <span>Recorded At</span>
                <span class="font-bold text-slate-300">
                    {{ $recordedAt ? $recordedAt->format('d M Y H:i') : 'Not recorded' }}
                </span>
            </div>
            <div class="flex justify-between gap-4">
                <span>Reference Source</span>
                <span class="font-bold text-right text-slate-300">
                    @if($sourceUrl)
                        <a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer" class="text-cyan-300 hover:text-cyan-200 underline decoration-cyan-700/60 underline-offset-2">
                            {{ $sourceName ?: 'Commodity reference' }}
                        </a>
                    @else
                        {{ $sourceName ?: 'Unavailable' }}
                    @endif
                </span>
            </div>
        </div>

        @if($showConditionUpdateForm ?? false)
            <details class="mt-5 group">
                <summary class="cursor-pointer list-none rounded-xl border border-slate-700 bg-slate-800/70 px-4 py-3 text-xs font-black text-slate-200 hover:bg-slate-800 transition-colors flex items-center justify-between">
                    <span>Update Recorded Condition</span>
                    <span class="text-cyan-400 group-open:rotate-45 transition-transform">+</span>
                </summary>

                <form method="POST" action="{{ route('shipments.conditions.update', $shipment) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <p class="text-[11px] leading-relaxed text-slate-400">
                        These are point-in-time operator records, not live IoT telemetry. Leave unknown values blank.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($isDry)
                            <label class="block">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Cargo Moisture (%)</span>
                                <input type="number" step="0.1" min="0" max="100"
                                       name="recorded_moisture_percent"
                                       value="{{ old('recorded_moisture_percent', $shipment->recorded_moisture_percent) }}"
                                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-950 text-sm font-bold text-white focus:border-cyan-500 focus:ring-cyan-500">
                            </label>
                        @else
                            <label class="block">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Cargo Temperature (°C)</span>
                                <input type="number" step="0.1" min="-50" max="80"
                                       name="recorded_temperature_c"
                                       value="{{ old('recorded_temperature_c', $shipment->recorded_temperature_c) }}"
                                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-950 text-sm font-bold text-white focus:border-cyan-500 focus:ring-cyan-500">
                            </label>
                        @endif

                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Relative Humidity (% RH)</span>
                            <input type="number" step="0.1" min="0" max="100"
                                   name="recorded_relative_humidity_percent"
                                   value="{{ old('recorded_relative_humidity_percent', $shipment->recorded_relative_humidity_percent) }}"
                                   class="mt-1 w-full rounded-xl border-slate-700 bg-slate-950 text-sm font-bold text-white focus:border-cyan-500 focus:ring-cyan-500">
                        </label>
                    </div>

                    @if(!$isDry)
                        <input type="hidden" name="recorded_moisture_percent" value="{{ $shipment->recorded_moisture_percent }}">
                    @else
                        <input type="hidden" name="recorded_temperature_c" value="{{ $shipment->recorded_temperature_c }}">
                    @endif

                    <button type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-3 text-xs font-black text-slate-950 hover:bg-cyan-400 transition-colors">
                        Save Condition & Reassess
                    </button>
                </form>
            </details>
        @endif
    </div>
</div>
