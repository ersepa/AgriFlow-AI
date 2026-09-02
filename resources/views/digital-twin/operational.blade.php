<x-app-layout>
    @php
        $modelType =
            data_get(
                $baseline,
                'analysis.quality_prediction.condition_model_type'
            )
            ?? data_get(
                $baseline,
                'analysis.commodity_profile.quality_model_type'
            );

        $isDry = $modelType === 'storage_stability';
    @endphp

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6">

        {{-- PAGE HEADER (WARNA TEKS DIUBAH KE SLATE-900 AGAR TERBACA TERANG BENDERANG) --}}
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5 mb-8">
            <div>
                <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-600">
                    DeveloperDay 2026 · Road to APICTA
                </p>
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-900 mt-2">
                    Operational Digital Twin
                </h1>
                <p class="text-sm text-slate-600 max-w-3xl mt-3 leading-relaxed font-medium">
                    Compare up to three operational scenarios against the current shipment plan. AgriFlow reruns the same deterministic commodity, risk, and routing engines for every option before recommending a change.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('digital-twin.comparisons.history') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-700/80 bg-slate-900 hover:bg-slate-800 text-xs font-black uppercase tracking-widest text-cyan-400 hover:text-white transition-all duration-200 shadow-lg">
                    Comparison History
                </a>
                <a href="{{ route('digital-twin.scenarios.history') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-700/80 bg-slate-900 hover:bg-slate-800 text-xs font-black uppercase tracking-widest text-indigo-400 hover:text-white transition-all duration-200 shadow-lg">
                    Scenario History
                </a>
            </div>
        </div>

        @if(!$shipment)
            <div class="rounded-3xl border border-amber-500/20 bg-amber-500/10 p-8 shadow-xl">
                <p class="font-black text-amber-300 text-sm">
                    No active shipment is available for simulation.
                </p>
            </div>
        @else
            {{-- SHIPMENT METRICS BAR --}}
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl relative overflow-hidden mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-stretch relative z-10">
                    
                    {{-- Select Box --}}
                    <div class="lg:col-span-2 bg-slate-950/70 p-5 rounded-2xl border border-slate-800 flex flex-col justify-center">
                        <label class="text-[10px] uppercase font-black tracking-widest text-cyan-400">
                            Select Active Shipment
                        </label>

                        <select id="shipmentSelector"
                                class="mt-2 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-sm font-bold focus:ring-cyan-500 focus:border-cyan-500 shadow-inner">
                            @foreach($shipments as $item)
                                <option value="{{ $item->id }}"
                                    @selected($shipment->id === $item->id)>
                                    #{{ $item->id }} · {{ $item->harvest?->commodity ?? 'Unknown' }} · {{ $item->origin }} → {{ $item->destination }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Metric 1: Risk --}}
                    <div class="bg-slate-950/70 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Current Risk</p>
                        <p class="text-3xl font-black text-white mt-2">
                            {{ data_get($baseline, 'analysis.risk_score', '—') }}<span class="text-xs text-slate-500 font-bold">/100</span>
                        </p>
                    </div>

                    {{-- Metric 2: Condition --}}
                    <div class="bg-slate-950/70 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">
                            {{ $isDry ? 'Current Condition' : 'Arrival Quality' }}
                        </p>
                        <p class="text-base font-black text-emerald-400 mt-2 leading-snug">
                            @if($isDry)
                                {{ data_get(
                                    $baseline,
                                    'analysis.quality_prediction.storage_stability_assessment.status',
                                    'Condition evidence required'
                                ) }}
                            @else
                                {{ data_get($baseline, 'analysis.quality_at_arrival') !== null
                                    ? data_get($baseline, 'analysis.quality_at_arrival') . '/100'
                                    : 'Not estimated' }}
                            @endif
                        </p>
                    </div>

                    {{-- Metric 3: Feasibility --}}
                    <div class="bg-slate-950/70 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Feasibility</p>
                        <p class="text-xl font-black text-cyan-400 mt-2 uppercase tracking-wide">
                            {{ data_get($baseline, 'route.freshness_feasibility', '—') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                {{-- LEFT COLUMN: SCENARIO BUILDER --}}
                <div class="xl:col-span-5">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-black tracking-widest text-cyan-400">
                                    Scenario Builder
                                </p>
                                <h2 class="text-xl font-black text-white mt-1">
                                    Compare A / B / C
                                </h2>
                                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                                    Vehicle labels do not apply hidden bonuses. Scenario differences come only from explicit route, delay, and condition inputs supported by the current models.
                                </p>
                            </div>

                            <button type="button"
                                    id="addScenario"
                                    class="rounded-xl border border-cyan-500/30 bg-cyan-500/10 hover:bg-cyan-500/20 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-cyan-400 transition-all shrink-0">
                                + Scenario
                            </button>
                        </div>

                        <form id="multiScenarioForm" class="mt-6">
                            @csrf
                            <div id="scenarioCards" class="space-y-4"></div>

                            <button type="submit"
                                    id="compareButton"
                                    class="w-full mt-6 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-4 text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-600/30">
                                Compare Scenarios
                            </button>

                            <p id="compareError"
                               class="hidden text-xs font-bold text-rose-400 mt-3"></p>
                        </form>
                    </div>
                </div>

                {{-- RIGHT COLUMN: COMPARISON RESULTS --}}
                <div class="xl:col-span-7">
                    {{-- EMPTY STATE (WARNA DIBETULKAN DARI SAMAR KE KONTRAS JELAS) --}}
                    <div id="comparisonEmpty"
                         class="rounded-3xl border border-dashed border-slate-700 bg-slate-900 min-h-[560px] flex items-center justify-center p-10 text-center shadow-2xl">
                        <div>
                            <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 text-cyan-400 flex items-center justify-center mx-auto mb-4 shadow-md">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-xs uppercase font-black tracking-widest text-cyan-400">
                                Decision Comparison
                            </p>
                            <h2 class="text-2xl font-black text-white mt-2">
                                Build at least one scenario
                            </h2>
                            <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto leading-relaxed">
                                Current Plan always remains a decision option. A scenario is recommended only when it materially improves the baseline under Step 6.1 decision semantics.
                            </p>
                        </div>
                    </div>

                    {{-- ACTIVE COMPARISON PANEL --}}
                    <div id="comparisonPanel"
                         class="hidden space-y-6">
                        
                        {{-- DECISION BANNER --}}
                        <div class="rounded-3xl bg-slate-900 border border-slate-800 text-white p-7 shadow-2xl relative overflow-hidden">
                            <p class="text-[10px] uppercase font-black tracking-[0.22em] text-cyan-400">
                                Multi-Scenario Decision
                            </p>
                            <h2 id="comparisonStatus"
                                class="text-2xl font-black mt-2"></h2>
                            <p id="comparisonReason"
                               class="text-sm text-slate-300 mt-3 leading-relaxed font-medium"></p>
                            <p id="preferredDecision"
                               class="text-xs font-black uppercase tracking-widest text-emerald-400 mt-4 bg-emerald-500/10 inline-block px-3 py-1 rounded-lg border border-emerald-500/20"></p>
                        </div>

                        {{-- COMPARISON TABLE --}}
                        <div class="overflow-x-auto rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl">
                            <table class="w-full min-w-[820px] text-left border-collapse">
                                <thead class="bg-slate-950/80 border-b border-slate-800">
                                    <tr>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Option</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Decision</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Risk</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Condition</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Feasibility</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Margin</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Carbon</th>
                                        <th class="p-4 text-[9px] uppercase font-black tracking-widest text-slate-400">Evidence</th>
                                    </tr>
                                </thead>
                                <tbody id="comparisonRows" class="divide-y divide-slate-800/60"></tbody>
                            </table>
                        </div>

                        {{-- DECISION RULE NOTICE --}}
                        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
                            <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">
                                Decision Rule
                            </p>
                            <p class="text-xs text-slate-300 leading-relaxed mt-2 font-medium">
                                AgriFlow does not create a new arbitrary weighted scenario score. Options are evaluated by feasibility first, then operational risk, condition outcome when available, transit margin, and estimated carbon.
                            </p>
                        </div>

                        {{-- SAVE FORM --}}
                        <form id="saveComparisonForm"
                              method="POST"
                              action="{{ route('digital-twin.comparisons.store', $shipment) }}"
                              class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
                            @csrf

                            <label class="text-xs font-black text-slate-300 uppercase tracking-wider">
                                Comparison Set Name
                            </label>
                            <input type="text"
                                   name="name"
                                   value="Operational Comparison"
                                   maxlength="120"
                                   class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white text-sm focus:ring-cyan-500 focus:border-cyan-500">

                            <input type="hidden"
                                   name="scenarios_json"
                                   id="scenariosJson">

                            <button type="submit"
                                    class="w-full mt-4 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 px-5 py-3.5 text-xs font-black uppercase tracking-widest text-cyan-400 hover:text-white transition-all shadow-md">
                                Save Comparison Snapshot
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($shipment)
        <script>
            const isDryCommodity = @json($isDry);
            const routeOptions = @json($routeOptions);
            const comparisonUrl = @json(route('digital-twin.compare', $shipment));

            const cardsContainer = document.getElementById('scenarioCards');
            const addScenarioButton = document.getElementById('addScenario');
            const form = document.getElementById('multiScenarioForm');
            const compareButton = document.getElementById('compareButton');
            const compareError = document.getElementById('compareError');
            const emptyState = document.getElementById('comparisonEmpty');
            const panel = document.getElementById('comparisonPanel');

            let scenarioCount = 0;

            function addScenario() {
                if (scenarioCount >= 3) return;

                const index = scenarioCount;
                const label = String.fromCharCode(65 + index);
                const card = document.createElement('div');

                card.className = 'scenario-card rounded-2xl border border-slate-800 bg-slate-950/70 p-5';
                card.dataset.index = index;

                const routeOptionsHtml = [
                    `<option value="0">Current Route</option>`,
                    ...routeOptions
                        .filter(option => {
                            const rank = Number(option.rank);
                            const labelStr = String(option.label || '').trim().toLowerCase();
                            const sourceStr = String(option.source || '').trim().toLowerCase();
                            const isCurrentRoute = labelStr === 'current route' || labelStr.startsWith('current route ·') || sourceStr === 'current';
                            return rank > 0 && !isCurrentRoute;
                        })
                        .map(option =>
                            `<option value="${escapeHtml(option.rank)}">${escapeHtml(option.label)} · ${Number(option.distance_km).toFixed(1)} km · ${Number(option.duration_hours).toFixed(2)} h</option>`
                        )
                ].join('');

                const conditionFields = isDryCommodity
                    ? `
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Cargo Moisture (%)</label>
                                <input type="number" data-field="moisture_percent" min="0" max="100" step="0.1" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Relative Humidity (%)</label>
                                <input type="number" data-field="relative_humidity_percent" min="0" max="100" step="0.1" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Storage Horizon</label>
                            <select data-field="storage_horizon" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                                <option value="short_term">Short-term</option>
                                <option value="long_term">Long-term</option>
                            </select>
                        </div>
                    `
                    : `
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Cargo Temperature (°C)</label>
                            <input type="number" data-field="temperature_c" min="-20" max="60" step="0.1" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                        </div>
                    `;

                card.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[9px] uppercase font-black tracking-widest text-cyan-400">
                            Scenario ${label}
                        </p>
                        ${index > 0 ? `<button type="button" class="remove-scenario text-[10px] font-black uppercase tracking-widest text-rose-400 hover:text-rose-300 transition-colors">Remove</button>` : ''}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Scenario Name</label>
                            <input type="text" data-field="name" value="Scenario ${label}" maxlength="120" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Route</label>
                            <select data-field="route_rank" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                                ${routeOptionsHtml}
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Additional Delay (h)</label>
                            <input type="number" data-field="delay_hours" value="0" min="0" max="168" step="0.25" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Vehicle Plan</label>
                            <select data-field="vehicle" class="mt-1 w-full rounded-xl border-slate-700 bg-slate-900 text-white text-xs">
                                <option value="standard_truck">Standard Truck</option>
                                <option value="refrigerated_truck">Refrigerated Truck</option>
                                <option value="electric_truck">Electric Truck</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3 mt-3">
                        ${conditionFields}
                    </div>
                `;

                cardsContainer.appendChild(card);
                card.querySelector('.remove-scenario')?.addEventListener('click', () => {
                    card.remove();
                    rebuildScenarioLabels();
                });

                scenarioCount = cardsContainer.querySelectorAll('.scenario-card').length;
                updateAddButton();
            }

            function rebuildScenarioLabels() {
                const cards = [...cardsContainer.querySelectorAll('.scenario-card')];
                cards.forEach((card, index) => {
                    card.dataset.index = index;
                    const badge = card.querySelector('p');
                    if (badge) badge.innerText = `Scenario ${String.fromCharCode(65 + index)}`;
                });
                scenarioCount = cards.length;
                updateAddButton();
            }

            function updateAddButton() {
                addScenarioButton.disabled = scenarioCount >= 3;
                addScenarioButton.classList.toggle('opacity-40', scenarioCount >= 3);
            }

            function collectScenarios() {
                return [...cardsContainer.querySelectorAll('.scenario-card')].map(card => {
                    const scenario = {};
                    card.querySelectorAll('[data-field]').forEach(input => {
                        const key = input.dataset.field;
                        const value = input.value;
                        scenario[key] = value === '' ? null : value;
                    });
                    return scenario;
                });
            }

            addScenarioButton.addEventListener('click', addScenario);

            document.getElementById('shipmentSelector').addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('shipment', this.value);
                window.location.href = url.toString();
            });

            form.addEventListener('submit', async event => {
                event.preventDefault();
                compareError.classList.add('hidden');
                compareButton.disabled = true;
                compareButton.innerText = 'Comparing...';

                const scenarios = collectScenarios();

                try {
                    const response = await fetch(comparisonUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                || document.querySelector('input[name="_token"]')?.value || '',
                        },
                        body: JSON.stringify({ scenarios }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const validation = data.errors ? Object.values(data.errors).flat().join(' ') : null;
                        throw new Error(validation || data.message || 'Comparison failed.');
                    }

                    renderComparison(data, scenarios);
                } catch (error) {
                    compareError.innerText = error.message;
                    compareError.classList.remove('hidden');
                } finally {
                    compareButton.disabled = false;
                    compareButton.innerText = 'Compare Scenarios';
                }
            });

            function renderComparison(data, scenarios) {
                emptyState.classList.add('hidden');
                panel.classList.remove('hidden');

                const comparison = data.comparison;
                document.getElementById('comparisonStatus').innerText = comparison.decision_status;
                document.getElementById('comparisonReason').innerText = comparison.decision_reason;

                const preferred = comparison.preferred_option === 'scenario'
                    ? (comparison.recommended_scenario?.name || 'Scenario')
                    : 'Keep Current Plan';

                document.getElementById('preferredDecision').innerText = `Preferred Decision: ${preferred}`;

                const tbody = document.getElementById('comparisonRows');
                tbody.innerHTML = '';

                const baseline = data.baseline;
                appendRow(tbody, {
                    name: 'Current Plan',
                    decision_status: comparison.preferred_option === 'current_plan' ? 'Preferred Baseline' : 'Baseline',
                    risk_score: baseline.analysis?.risk_score,
                    quality_at_arrival: baseline.analysis?.quality_at_arrival,
                    storage_status: baseline.analysis?.quality_prediction?.storage_stability_assessment?.status,
                    feasibility: baseline.route?.freshness_feasibility,
                    transit_margin_hours: baseline.route?.transit_margin_hours,
                    carbon_kg: baseline.carbon?.estimated_kg,
                    evidence_coverage: baseline.evidence?.percent,
                }, comparison.preferred_option === 'current_plan');

                (data.decision_table || []).forEach(row => {
                    const isPreferred = comparison.preferred_option === 'scenario' && comparison.recommended_scenario?.name === row.name;
                    appendRow(tbody, row, isPreferred);
                });

                document.getElementById('scenariosJson').value = JSON.stringify(scenarios);
            }

            function appendRow(tbody, row, preferred) {
                const tr = document.createElement('tr');
                tr.className = preferred ? 'bg-emerald-500/10 hover:bg-emerald-500/20 transition-colors' : 'hover:bg-slate-800/40 transition-colors';

                const condition = row.storage_status
                    || (row.quality_at_arrival !== null && row.quality_at_arrival !== undefined ? `${row.quality_at_arrival}/100` : 'Not estimated');

                tr.innerHTML = `
                    <td class="p-4 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-black text-white text-sm">${escapeHtml(row.name || 'Option')}</span>
                            ${preferred ? '<span class="inline-flex rounded-lg bg-emerald-500/20 border border-emerald-500/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-emerald-400">Preferred</span>' : ''}
                        </div>
                    </td>
                    <td class="p-4 align-top text-xs font-bold text-slate-300">${escapeHtml(row.decision_status || '—')}</td>
                    <td class="p-4 align-top text-sm font-black text-white">${row.risk_score ?? '—'}<span class="text-xs text-slate-500">/100</span></td>
                    <td class="p-4 align-top text-xs font-bold text-slate-300 max-w-[180px]">${escapeHtml(condition)}</td>
                    <td class="p-4 align-top text-xs font-black text-cyan-400 uppercase tracking-wide">${escapeHtml(row.feasibility || '—')}</td>
                    <td class="p-4 align-top text-xs font-bold text-slate-300">${formatNumber(row.transit_margin_hours, 1, ' h')}</td>
                    <td class="p-4 align-top text-xs font-bold text-slate-300">${formatNumber(row.carbon_kg, 2, ' kg')}</td>
                    <td class="p-4 align-top text-xs font-black text-cyan-400">${formatPercent(row.evidence_coverage)}</td>
                `;

                tbody.appendChild(tr);
            }

            function formatNumber(value, digits, suffix) {
                if (value === null || value === undefined) return 'Unavailable';
                return Number(value).toFixed(digits) + suffix;
            }

            function formatPercent(value) {
                if (value === null || value === undefined || value === '') return 'Unavailable';
                return `${Number(value).toFixed(0)}%`;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.innerText = String(value ?? '');
                return div.innerHTML;
            }

            addScenario();
        </script>
    @endif
</x-app-layout>