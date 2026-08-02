<x-app-layout>
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
        .ai-result-card { animation: slideIn 0.6s ease-out forwards; }
        .btn-animate {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-animate:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .btn-animate:active {
        transform: translateY(1px) scale(0.98);
    }
    </style>

<div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
    <div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">AI Intelligence Hub</h1>
        <p class="text-slate-500 mt-2 font-medium">Select a shipment to trigger predictive sustainability analysis.</p>
    </div>
    
    <a href="{{ route('ai-analysis.history') }}" 
       class="btn-animate group inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-black shadow-sm hover:bg-indigo-600 hover:text-white transition-all duration-300">
        
        <svg class="w-5 h-5 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        
        <span>View Analysis History</span>
    </a>
</div>
        
@php
    $predictionData = session('prediction_data', []);
@endphp

@if(session('ai_result'))
    @php 
        $ai = session('ai_result');
        $extract = function($label) use ($ai) {
            preg_match("/$label:\s*(.*)/i", $ai, $matches);
            return $matches[1] ?? 'N/A';
        };
        
$riskLevel = session('risk_level');
$wasteProb = session('waste_probability');
$sustainScore = session('sustainability_score');
    @endphp

    <div class="ai-result-card mb-10 bg-slate-900 p-8 rounded-3xl shadow-2xl border border-slate-800 text-white">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-xl font-black flex items-center gap-2">
                <span class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></span>
                Analysis Result
            </h2>
            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-indigo-500/20 text-indigo-300">OPENROUTER AI</span>
        </div>
@php
    $shipmentData = session('shipment_data');
@endphp

@if($shipmentData)
<div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-4">
        Shipment Data
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 text-sm">
        <div>
            <p class="text-slate-400">Commodity</p>
            <p class="font-bold">{{ $shipmentData['commodity'] }}</p>
        </div>

        <div>
            <p class="text-slate-400">Origin</p>
            <p class="font-bold">{{ $shipmentData['origin'] }}</p>
        </div>

        <div>
            <p class="text-slate-400">Destination</p>
            <p class="font-bold">{{ $shipmentData['destination'] }}</p>
        </div>

        <div>
            <p class="text-slate-400">Status</p>
            <p class="font-bold">{{ $shipmentData['status'] }}</p>
        </div>

        <div>
            <p class="text-slate-400">Distance</p>
            <p class="font-bold">{{ $shipmentData['distance'] }} km</p>
        </div>

        <div>
            <p class="text-slate-400">Remaining Days</p>
            <p class="font-bold">{{ $shipmentData['remaining_days'] }}</p>
        </div>
        <div>
    <p class="text-slate-400">Duration</p>
    <p class="font-bold">
        {{ $shipmentData['duration'] ?? 'N/A' }} Hours
    </p>
</div>
<div>
    <p class="text-slate-400">Carbon Emission</p>
    <p class="font-bold text-rose-400">
        {{ $shipmentData['carbon_emission'] ?? 'N/A' }} kg CO₂
    </p>
</div>
    </div>
</div>
@endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Risk Level</p>
                <span class="inline-block mt-2 px-4 py-1 rounded-full text-xs font-black uppercase
                    {{ str_contains($riskLevel, 'High') ? 'bg-rose-500/20 text-rose-400' : 
                       (str_contains($riskLevel, 'Medium') ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400') }}">
                    {{ $riskLevel }}
                </span>
            </div>
            
            <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Waste Probability</p>
                <p class="mt-2 text-lg font-black text-white">{{ $wasteProb }}</p>
            </div>

            <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sustainability</p>
                <p class="mt-2 text-lg font-black text-white">{{ $sustainScore }}</p>
            </div>
        </div>

        @if($predictionData)

        
        @endif
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    {{-- Route Visualization --}}
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">

        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-4">
            Route Visualization
        </h3>

        <div id="map" class="h-[320px] rounded-xl overflow-hidden"></div>

    </div>

    {{-- Predicted Spoilage Risk --}}
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">

        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-2">
            Predicted Spoilage Risk
        </h3>

        <p class="text-slate-400 text-sm mb-4">
            Example trend of spoilage risk as expiry approaches.
        </p>

        <div class="h-[320px]">
            <canvas id="riskChart"></canvas>
        </div>

    </div>

</div>
@if(session('explainability'))

@php
$drivers = session('explainability');
$totalImpact = collect($drivers)->sum('impact');
@endphp

<div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-3xl p-7">

    <div class="flex items-center justify-between mb-7">

        <div>

            <p class="text-xs uppercase tracking-[0.25em] text-indigo-400 font-black">
                AI Explainability
            </p>

            <h2 class="text-2xl font-black text-white mt-1">
                Why did AI produce this prediction?
            </h2>

        </div>

        <div class="px-4 py-2 rounded-xl bg-indigo-500/20 text-indigo-300 text-xs font-black">
            Decision Engine
        </div>

    </div>

    <div class="space-y-6">

        @foreach($drivers as $driver)

@php
    if($driver['impact'] >= 30){
        $barColor = 'bg-rose-500';
        $textColor = 'text-rose-400';
        $label = 'Critical';
    }elseif($driver['impact'] >= 20){
        $barColor = 'bg-amber-400';
        $textColor = 'text-amber-400';
        $label = 'High';
    }else{
        $barColor = 'bg-emerald-500';
        $textColor = 'text-emerald-400';
        $label = 'Medium';
    }
@endphp

        <div>

            <div class="flex justify-between items-center mb-2">

                <div class="flex items-center gap-3">

                    <span class="text-2xl">
                        {{ $driver['icon'] }}
                    </span>

                    <div>

                        <p class="text-white font-bold">
                            {{ $driver['title'] }}
                        </p>

                        <p class="text-slate-400 text-xs">
                            {{ $driver['reason'] }}
                        </p>

                    </div>

                </div>

                <div class="text-right">

                    <p class="text-white font-black">
                        {{ $driver['impact'] }}%
                    </p>

<p class="{{ $textColor }} text-xs uppercase font-bold">
    {{ $label }}
</p>

                </div>

            </div>

            <div class="h-2 rounded-full bg-slate-700 overflow-hidden">

<div
    class="h-full rounded-full transition-all duration-1000 {{ $barColor }}"
    style="width: {{ $driver['impact'] }}%;">
</div>

            </div>

        </div>

        @endforeach

    </div>

    <div class="mt-8 border-t border-slate-700 pt-6">

        <div class="flex justify-between mb-2">

            <span class="text-slate-400 font-bold">
                AI Confidence
            </span>

            <span class="text-white font-black">
                {{ min(100,$totalImpact) }}%
            </span>

        </div>

        <div class="h-3 rounded-full bg-slate-700 overflow-hidden">

            <div
                class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 transition-all duration-1000"
                style="width:{{ min(100,$totalImpact) }}%">
            </div>

        </div>

        <p class="mt-5 text-slate-300 leading-7">

            <span class="font-bold text-white">
                Primary Cause:
            </span>

            {{ $drivers[0]['reason'] }}

            This factor contributed the most to the overall AI prediction.

        </p>

    </div>

</div>
<div class="mt-8 bg-slate-900 rounded-3xl p-8 border border-slate-700">

    <h2 class="text-xl font-black text-white mb-8">
        AI Decision Score
    </h2>

    <div class="flex flex-col items-center">

        <div class="relative w-56 h-56">

            <svg class="w-full h-full -rotate-90">

                <circle
                    cx="112"
                    cy="112"
                    r="90"
                    stroke="#334155"
                    stroke-width="16"
                    fill="none"/>

                @php

                    $circumference = 2 * pi() * 90;

                    $offset = $circumference -
                    ($circumference * session('priority_score',0)/100);

                @endphp

                <circle
                    cx="112"
                    cy="112"
                    r="90"
                    stroke="#6366f1"
                    stroke-width="16"
                    fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    class="transition-all duration-1000"/>

            </svg>

            <div class="absolute inset-0 flex flex-col items-center justify-center">

                <p class="text-6xl font-black text-white">

                    {{ session('priority_score') }}

                </p>

                <p class="text-indigo-400 uppercase font-bold tracking-widest">

                    HIGH PRIORITY

                </p>

            </div>

        </div>

    </div>

</div>
<div class="mt-8">

    <h3 class="text-sm uppercase tracking-widest text-slate-400 font-black mb-5">

        Decision Breakdown

    </h3>

    @foreach($drivers as $driver)

    <div class="flex justify-between items-center py-3 border-b border-slate-800">

        <div class="flex items-center gap-3">

            <span class="text-xl">

                {{ $driver['icon'] }}

            </span>

            <span class="text-white font-bold">

                {{ $driver['title'] }}

            </span>

        </div>

        <span class="text-indigo-400 font-black">

            +{{ $driver['impact'] }}

        </span>

    </div>

    @endforeach

</div>
<div class="mt-8 grid grid-cols-2 gap-4">

<div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-5">

<p class="text-xs uppercase text-slate-400">

Overall Impact

</p>

<p class="text-3xl font-black text-white">

{{ session('total_impact') }}

</p>

</div>

<div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5">

<p class="text-xs uppercase text-slate-400">

Decision

</p>

<p class="text-xl font-black text-emerald-400">

Dispatch Now

</p>

</div>

</div>
@endif
        <div class="pt-6 border-t border-slate-700">
            <p class="text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Recommendations</p>
            <div class="text-sm text-indigo-100/80 font-medium leading-relaxed italic">
                {!! nl2br(e(explode('Recommendations:', $ai)[1] ?? 'No recommendations')) !!}
            </div>
        </div>
    </div>
@endif
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-4 px-2">
            <thead>
                <tr class="bg-slate-900 text-white uppercase text-[10px] font-black tracking-widest">
                    <th class="px-8 py-5 rounded-l-2xl">Commodity</th>
                    <th class="px-8 py-5">Route</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="w-[25%] px-8 py-5 rounded-r-2xl text-right pr-20">Control</th>
                </tr>
            </thead>
            
            <tbody class="text-sm">
                @foreach($shipments as $shipment)
                <tr class="bg-white border border-slate-100 shadow-sm hover:shadow-lg hover:border-indigo-100 transition-all duration-300 rounded-3xl">
                    
                    <td class="px-8 py-6 font-black text-slate-900 text-lg rounded-l-2xl">
                        {{ $shipment->harvest->commodity }}
                    </td>
                    
                    <td class="px-8 py-6 font-bold text-slate-600">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <span>{{ $shipment->origin }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            <div class="flex items-center gap-1.5 text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                <span>{{ $shipment->destination }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-8 py-6">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-sm
                            @if($shipment->status == 'Delivered') bg-emerald-50 text-emerald-600 border-emerald-100
                            @elseif($shipment->status == 'In Transit') bg-blue-50 text-blue-600 border-blue-100
                            @elseif($shipment->status == 'Packed') bg-purple-50 text-purple-600 border-purple-100
                            @elseif($shipment->status == 'Harvested') bg-amber-50 text-amber-600 border-amber-100
                            @else bg-slate-50 text-slate-600 border-slate-200 @endif">
                            {{ $shipment->status }}
                        </span>
                    </td>

                    <td class="px-8 py-6 text-right rounded-r-2xl">
                        <form
action="{{ route('ai.analysis.run',$shipment->id) }}"
method="POST"
onsubmit="showLoading(this)">
                            @csrf
<button
type="submit"
class="analyzeBtn inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all shadow-lg shadow-indigo-200 hover:scale-[1.02]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Analyze Now
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@php
    $shipmentData = session('shipment_data');
    $predictionData = session('prediction_data');
@endphp
@if($shipmentData)
@php
$routeGeometry = json_decode(
    $shipmentData['route_geometry'],
    true
);
@endphp
<script>

document.addEventListener('DOMContentLoaded', function() {

    const originLat =
        {{ $shipmentData['origin_lat'] }};

    const originLng =
        {{ $shipmentData['origin_lng'] }};

    const destinationLat =
        {{ $shipmentData['destination_lat'] }};

    const destinationLng =
        {{ $shipmentData['destination_lng'] }};

    const map = L.map('map').setView(
        [originLat, originLng],
        6
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    ).addTo(map);

    L.marker([originLat, originLng])
        .addTo(map)
        .bindPopup('Origin');

    L.marker([destinationLat, destinationLng])
        .addTo(map)
        .bindPopup('Destination');

const routeCoords = @json($routeGeometry);

const latlngs = routeCoords.map(coord => [
    coord[1],
    coord[0]
]);

const routeLine = L.polyline(latlngs).addTo(map);

map.fitBounds(
    routeLine.getBounds()
);

L.polyline(
    latlngs,
    {
        weight: 5
    }
).addTo(map);

});

</script>
@endif
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(session()->has('prediction_data'))

<script>

const predictionData = @json($predictionData);

const labels = predictionData.map(
    item => 'Day ' + item.day
);

const risks = predictionData.map(
    item => item.risk
);

new Chart(
    document.getElementById('riskChart'),
    {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Spoilage Risk (%)',
                data: risks,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    min: 0,
                    max: 100
                }
            }
        }
    }
);

</script>
<script>

function showLoading(form){

    document
        .getElementById('loadingOverlay')
        .classList.remove('hidden');

    document
        .getElementById('loadingOverlay')
        .classList.add('flex');

    const btn=form.querySelector("button");

    btn.disabled=true;

    btn.innerHTML=`
        <svg class="w-4 h-4 animate-spin"
            fill="none"
            viewBox="0 0 24 24">

            <circle
                cx="12"
                cy="12"
                r="10"
                stroke="white"
                stroke-width="3"
                opacity=".2"/>

            <path
                d="M22 12a10 10 0 00-10-10"
                stroke="white"
                stroke-width="3"/>

        </svg>

        Analyzing...
    `;

    return true;
}

</script>

@endif
<!-- AI Loading Overlay -->
<div id="loadingOverlay"
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center z-[9999]">

    <div class="bg-slate-900 border border-slate-700 rounded-3xl p-10 w-[420px] text-center shadow-2xl">

        <div class="flex justify-center mb-6">

            <div class="relative w-16 h-16">

                <div class="absolute inset-0 rounded-full border-4 border-indigo-900"></div>

                <div class="absolute inset-0 rounded-full border-4 border-indigo-500 border-t-transparent animate-spin"></div>

            </div>

        </div>

        <h2 class="text-2xl font-black text-white">
            AI is analyzing...
        </h2>

        <p class="text-slate-400 mt-3 leading-relaxed">
            AgriFlow AI is evaluating logistics risk,
            sustainability score,
            spoilage prediction,
            and shipment recommendations.
        </p>

        <div class="mt-8">

            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">

                <div class="h-full bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 animate-pulse"></div>

            </div>

        </div>

    </div>

</div>
</x-app-layout>