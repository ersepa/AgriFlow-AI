<x-app-layout>

    <style>

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .animate-card { animation: fadeIn 0.5s ease-out forwards; opacity: 0; }

        .delay-1 { animation-delay: 0.1s; } .delay-2 { animation-delay: 0.2s; } .delay-3 { animation-delay: 0.3s; }

        [x-cloak] { display: none !important; }

        .glass-card { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }

        .custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(99,102,241,0.8);
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(99,102,241,.5);
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(99,102,241,.8);
}

<style>

.earth{

animation:

floating 6s ease-in-out infinite;

box-shadow:

0 0 80px rgba(6,182,212,.35);

}

@keyframes floating{

0%{

transform:translateY(0px);

}

50%{

transform:translateY(-14px);

}

100%{

transform:translateY(0px);

}

}

.earth{
    animation: earthRotate 35s linear infinite;
}

@keyframes earthRotate{

    from{
        transform:rotate(0deg);
    }

    to{
        transform:rotate(360deg);
    }

}

.weather-tab{

padding:12px 20px;

border-radius:9999px;

background:#0f172a;

border:1px solid rgba(255,255,255,.08);

color:#94a3b8;

transition:.35s;

font-weight:700;

}

.weather-tab:hover{

transform:translateY(-2px);

border-color:#22d3ee;

color:white;

}

.weather-tab.active{

background:linear-gradient(90deg,#06b6d4,#22c55e);

color:white;

border-color:transparent;

box-shadow:0 0 25px rgba(34,211,238,.25);

}

</style>

    </style>



    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-slate-950 min-h-screen">

        <!-- Header -->

        <div class="mb-10">

            <h1 class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-indigo-500 to-cyan-400 tracking-tight">

                AgriFlow AI Dashboard

            </h1>

            <p class="text-slate-400 mt-2 font-medium tracking-wide">Real-time agriculture intelligence and predictive analytics.</p>

        </div>



        <!-- KPI Grid -->

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">

            @php

$items = [ ['Total Harvest', $totalHarvests, 'text-white'], ['Total Weight', number_format($totalWeight, 0, ',', '.') . ' KG', 'text-white'], ['Shipments', $totalShipments, 'text-white'], ['Delivered', $deliveredShipments, 'text-emerald-400'], ['AI Analyses', $totalAnalyses, 'text-indigo-400'], ['High Risk', $highRisk, 'text-rose-400'] ];

            @endphp

            @foreach($items as $i => $item)

                @php

                    $content = "Detail informasi untuk " . $item[0] . ".";

// Wrapper buat kotak modal biar konsisten

$wrapperStart = "<div style='background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 12px;'>";

$wrapperEnd = "</div>";

$emptyMsg = "<div style='color: #94a3b8; font-style: italic; text-align: center;'>Data tidak tersedia saat ini.</div>";



switch ($item[0]) {

    case 'Total Harvest':

        $shipments = \App\Models\Shipment::with('harvest')->get();

        if ($shipments->isEmpty()) {

            $content = $wrapperStart . $emptyMsg . $wrapperEnd;

        } else {

            $list = $shipments->pluck('harvest.commodity')->unique()->implode(', ');

            $content = $wrapperStart . "Terdapat total <strong>" . $totalHarvests . "</strong> data panen.<br><br>Komoditas: <strong>" . $list . "</strong>" . $wrapperEnd;

        }

        break;



    case 'Total Weight':

        $weightDetails = \App\Models\Harvest::select('commodity')->selectRaw('SUM(weight) as total_weight')->groupBy('commodity')->get();

        if ($weightDetails->isEmpty()) {

            $content = $wrapperStart . $emptyMsg . $wrapperEnd;

        } else {

            $inner = "<p style='margin-bottom:8px'>Detail berat per komoditas:</p><ul style='list-style: disc; margin-left: 20px;'>";

            foreach ($weightDetails as $w) { $inner .= "<li><strong>" . $w->commodity . "</strong>: " . number_format($w->total_weight, 0) . " KG</li>"; }

            $inner .= "</ul>";

            $content = $wrapperStart . $inner . $wrapperEnd;

        }

        break;



    case 'Shipments':

    $recentShipments = \App\Models\Shipment::with('harvest')
        ->latest()
        ->take(20)
        ->get();

    if ($recentShipments->isEmpty()) {

        $content = $wrapperStart . $emptyMsg . $wrapperEnd;

    } else {

        $inner = "
        <p style='margin-bottom:12px;font-weight:bold'>
            Recent Shipments
        </p>

        <div class='custom-scrollbar'
            style='
                max-height:320px;
                overflow-y:auto;
                border:1px solid #e5e7eb;
                border-radius:12px;
                padding:12px;
            '>

        <ul style='list-style:none;padding:0;margin:0'>
        ";

        foreach ($recentShipments as $s) {

            $statusColor = match($s->status){
                'Harvested' => '#f59e0b',
                'Packed' => '#3b82f6',
                'In Transit' => '#8b5cf6',
                'Delivered' => '#22c55e',
                default => '#94a3b8',
            };

            $inner .= "

            <li style='
                padding:12px;
                margin-bottom:10px;
                border:1px solid #e2e8f0;
                border-radius:12px;
            '>

                <strong>{$s->harvest->commodity}</strong><br>

                <span style='font-size:12px;color:#64748b'>
                    {$s->origin} ➜ {$s->destination}
                </span>

                <br><br>

                <span
                    style='
                        display:inline-block;
                        background:{$statusColor};
                        color:white;
                        padding:3px 10px;
                        border-radius:999px;
                        font-size:11px;
                        font-weight:bold;
                    '>
                    {$s->status}
                </span>

            </li>

            ";
        }

        $inner .= "
        </ul>

        <div style='margin-top:15px;text-align:center'>
            Showing latest 20 shipments
        </div>

        </div>
        ";

        $content = $wrapperStart . $inner . $wrapperEnd;
    }

    break;



    case 'Delivered':

        $deliveredList = \App\Models\Shipment::where('status', 'Delivered')->with('harvest')->latest()->take(5)->get();

        if ($deliveredList->isEmpty()) {

            $content = $wrapperStart . $emptyMsg . $wrapperEnd;

        } else {

            $inner = "<p style='margin-bottom:8px'>5 Pengiriman berhasil:</p><ul style='list-style: disc; margin-left: 20px;'>";

            foreach ($deliveredList as $s) { $inner .= "<li><strong>" . ($s->harvest->commodity ?? 'N/A') . "</strong></li>"; }

            $inner .= "</ul>";

            $content = $wrapperStart . $inner . $wrapperEnd;

        }

        break;



    case 'AI Analyses':

$recentAnalyses = \App\Models\AiAnalysis::with('shipment.harvest')
    ->latest()
    ->take(20)
    ->get();

        if ($recentAnalyses->isEmpty()) {

            $content = $wrapperStart . $emptyMsg . $wrapperEnd;

        } else {

$inner = "
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(
        to bottom,
        #6366f1,
        #4f46e5
    );
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
        to bottom,
        #4f46e5,
        #4338ca
    );
}
</style>

<p style='margin-bottom:8px'>Recent AI Analyses</p>

<div class='custom-scrollbar'
     style='
        max-height:300px;
        overflow-y:auto;
        padding-right:8px;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:12px;
     '>

<ul style='list-style:none;padding:0'>
";

foreach ($recentAnalyses as $a) {

    $commodity = $a->shipment->harvest->commodity ?? 'Unknown';

    $inner .= "
<li style='
    margin-bottom:12px;
    padding:12px;
    border-radius:12px;
    border:1px solid #e2e8f0;
'>

        <strong>Commodity:</strong> {$commodity}<br>

        <strong>Risk:</strong>
        <span style='color:" .
            ($a->risk_level === 'High'
                ? '#ef4444'
                : ($a->risk_level === 'Medium'
                    ? '#f59e0b'
                    : '#22c55e')) .
        "'>
            {$a->risk_level}
        </span>
        <br>

        <strong>Waste:</strong> {$a->waste_probability}<br>

        <strong>Score:</strong> {$a->sustainability_score}

    </li>";
}

$inner .= "
</ul>
<div style='margin-top:15px; text-align:center;'>
<a href='" . route('ai-analysis.history') . "'
   class='inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 hover:-translate-y-0.5 transition-all duration-300'>
    View All Analyses →
</a>
</div>

</div>
</div>
";

            $content = $wrapperStart . $inner . $wrapperEnd;

        }

        break;



    case 'High Risk':

        $highRiskItems = \App\Models\AiAnalysis::where('risk_level', 'High')->with('shipment.harvest')->take(5)->get();

        if ($highRiskItems->isEmpty()) {

            $content = $wrapperStart . $emptyMsg . $wrapperEnd;

        } else {

            $inner = "<p style='margin-bottom:8px'>Daftar risiko tinggi:</p><ul style='list-style: disc; margin-left: 20px;'>";

            foreach ($highRiskItems as $h) { $inner .= "<li>Komoditas: <strong>" . ($h->shipment->harvest->commodity ?? 'N/A') . "</strong></li>"; }

            $inner .= "</ul>";

            $content = $wrapperStart . $inner . $wrapperEnd;

        }

        break;

}

                @endphp

<div onclick="openModal(this)"

     data-title="{{ $item[0] }}"

     data-content='{!! htmlspecialchars($content) !!}'

         class="glass-card p-6 rounded-3xl cursor-pointer hover:bg-slate-800 transition-all hover:scale-[1.02]">

        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $item[0] }}</p>

        <p class="text-2xl font-black mt-2 {{ $item[2] }}">{{ $item[1] }}</p>

    </div>

            @endforeach

        </div>

{{-- ========================================================= --}}
{{-- LIVE ENVIRONMENTAL INTELLIGENCE --}}
{{-- ========================================================= --}}

<div class="mt-12">

<div class="relative overflow-hidden rounded-[42px]
bg-gradient-to-br from-[#07111d] via-[#081725] to-[#050b14]
border border-cyan-500/20
shadow-[0_0_80px_rgba(6,182,212,.15)]">

    {{-- Glow --}}
    <div class="absolute -top-40 -right-20 w-[420px] h-[420px] bg-cyan-500/15 blur-[160px]"></div>
    <div class="absolute -bottom-32 -left-20 w-[350px] h-[350px] bg-indigo-500/15 blur-[140px]"></div>

    <div class="relative p-10 lg:p-14">

        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">

            <div>

                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-400/20">

                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>

                    <span class="uppercase tracking-[0.45em] text-cyan-300 text-xs font-black">
                        LIVE ENVIRONMENT
                    </span>

                </div>

                <h1 class="mt-6 text-5xl lg:text-6xl font-black text-white leading-tight">

                    Environmental Intelligence

                </h1>

                <p class="mt-5 max-w-3xl text-slate-400 text-lg leading-8">

                    AI continuously monitors weather conditions, route quality,
                    environmental impact, and shipment safety in real time.

                </p>

            </div>

            <div class="mt-8 lg:mt-0">

                <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/10 px-6 py-5">

                    <p class="text-emerald-300 uppercase tracking-[0.3em] text-xs font-black">

                        STATUS

                    </p>

                    <h2 class="mt-3 text-3xl font-black text-white">

                        ONLINE

                    </h2>

                    <p class="mt-2 text-slate-400">

                        Last Sync :
                        <span id="environmentTime">{{ now()->format('H:i:s') }}</span>

                    </p>

                </div>

            </div>

        </div>

        {{-- divider --}}

        <div class="my-12 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>

        {{-- SECTION 2 AKAN MASUK DI SINI --}}
        <div class="grid xl:grid-cols-[340px_1fr] gap-8">

    {{-- ========================================= --}}
    {{-- EARTH --}}
    {{-- ========================================= --}}

    <div
    class="relative overflow-hidden rounded-[36px]
    border border-cyan-500/20
    bg-gradient-to-br
    from-slate-900
    via-[#071827]
    to-slate-950
    p-8">

        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(6,182,212,.15),transparent_70%)]"></div>

        <div class="relative">

            <div class="sticky top-5 z-20 mb-8 flex items-center justify-between rounded-2xl border border-cyan-500/10 bg-slate-950/60 backdrop-blur-xl px-8 py-5">

                <div>

                    <p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">

                        SATELLITE

                    </p>

                    <h2 class="mt-3 text-white text-2xl font-black">

                        Earth Monitor

                    </h2>

                </div>

                <div class="rounded-full bg-cyan-500/10 px-4 py-2 border border-cyan-500/20">

                    <span class="text-cyan-300 text-xs font-bold">

                        CONNECTED

                    </span>

                </div>

            </div>

            <div class="flex justify-center mt-8">

                <div class="relative w-56 h-56 mx-auto">

                    <div
                    class="absolute inset-0 rounded-full
                    bg-cyan-400/30 blur-[80px] animate-pulse">
                    </div>

<div
    id="weatherOrb"
    class="w-60 h-60 mx-auto"

    data-rain="{{ $environment['weather']['rain'] ?? 0 }}"
    data-cloud="{{ $environment['weather']['cloud_cover'] ?? 0 }}"
    data-wind="{{ $environment['weather']['wind_speed_10m'] ?? 0 }}"
    data-temp="{{ $environment['weather']['temperature_2m'] ?? 25 }}">

</div>

                </div>

            </div>

<div class="grid grid-cols-2 gap-5 mt-8">

    <div
    class="rounded-2xl
    border border-white/5
    bg-white/5
    p-4">

        <p class="text-slate-400 text-sm">

            Location

        </p>

        <h2 class="mt-2 text-white font-black text-xl">

            {{ data_get($environment, 'location', 'Unknown') }}

        </h2>

    </div>

    <div
    class="rounded-2xl
    border border-white/5
    bg-white/5
    p-4">

        <p class="text-slate-400 text-sm">

            Last Update

        </p>

        <h2 class="mt-2 text-cyan-300 font-black text-xl">

            {{ $environment['updated_at'] }}

        </h2>

    </div>

    <div
    class="rounded-2xl
    border border-white/5
    bg-white/5
    p-4">

        <p class="text-slate-400 text-sm">

            Data Source

        </p>

        <h2 class="mt-2 text-emerald-400 font-black">

            Open-Meteo API

        </h2>

    </div>

    <div
    class="rounded-2xl
    border border-white/5
    bg-white/5
    p-4">

        <p class="text-slate-400 text-sm">

            AI Status

        </p>

        <h2 class="mt-2 text-cyan-400 font-black">

            ONLINE

        </h2>

    </div>

</div>

        </div>

    </div>



{{-- ========================================= --}}
{{-- WEATHER COMMAND CENTER --}}
{{-- ========================================= --}}

<div
class="rounded-[36px]
border border-cyan-500/20
bg-gradient-to-br
from-slate-900
via-slate-950
to-black
p-8
relative
overflow-hidden">

<div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(6,182,212,.12),transparent_45%)]"></div>

<div class="relative">

<div class="flex items-start justify-between">

<div>

<p class="uppercase tracking-[0.4em] text-cyan-400 text-xs font-black">

LIVE WEATHER COMMAND CENTER

</p>

<h1
class="mt-4
text-5xl
font-black
text-white">

{{ round($environment['weather']['temperature_2m']) }}°C

</h1>

<p class="mt-2 text-slate-400">

Real-Time Environmental Intelligence

</p>

</div>

<div
class="rounded-2xl
border border-emerald-500/20
bg-emerald-500/10
px-5
py-3
text-right">

<div class="flex items-center gap-2 justify-end">

<div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>

<span class="text-emerald-300 font-bold">

LIVE

</span>

</div>

<p class="mt-2 text-xs text-slate-400">

Updated {{ $environment['updated_at'] }}

</p>

</div>

</div>

{{-- KPI GRID --}}

<div class="grid grid-cols-2 xl:grid-cols-3 gap-5 mt-10">

<div class="rounded-2xl border border-white/5 bg-white/5 backdrop-blur-xl p-5 transition-all duration-500 hover:-translate-y-2 hover:border-cyan-400/30 hover:shadow-[0_0_35px_rgba(34,211,238,.15)]">

<p class="text-slate-500 text-sm">

🌡 Temperature

</p>

<h2 class="mt-3 text-3xl font-black text-white">

{{ round($environment['weather']['temperature_2m']) }}°C

</h2>

</div>

<div class="rounded-2xl border border-white/5 bg-white/5 backdrop-blur-xl p-5 transition-all duration-500 hover:-translate-y-2 hover:border-cyan-400/30 hover:shadow-[0_0_35px_rgba(34,211,238,.15)]">

<p class="text-slate-500 text-sm">

💧 Humidity

</p>

<h2 class="mt-3 text-3xl font-black text-cyan-300">

{{ $environment['weather']['relative_humidity_2m'] }}%

</h2>

</div>

<div class="rounded-2xl border border-white/5 bg-white/5 backdrop-blur-xl p-5 transition-all duration-500 hover:-translate-y-2 hover:border-cyan-400/30 hover:shadow-[0_0_35px_rgba(34,211,238,.15)]">

<p class="text-slate-500 text-sm">

🌧 Rain

</p>

<h2 class="mt-3 text-3xl font-black text-blue-300">

{{ $environment['weather']['rain'] }} mm

</h2>

</div>

<div class="rounded-2xl border border-white/5 bg-white/5 backdrop-blur-xl p-5 transition-all duration-500 hover:-translate-y-2 hover:border-cyan-400/30 hover:shadow-[0_0_35px_rgba(34,211,238,.15)]">

<p class="text-slate-500 text-sm">

🌬 Wind

</p>

<h2 class="mt-3 text-3xl font-black text-white">

{{ round($environment['weather']['wind_speed_10m']) }}

<span class="text-lg">

km/h

</span>

</h2>

</div>

<div class="rounded-2xl border border-white/5 bg-white/5 backdrop-blur-xl p-5 transition-all duration-500 hover:-translate-y-2 hover:border-cyan-400/30 hover:shadow-[0_0_35px_rgba(34,211,238,.15)]">

<p class="text-slate-500 text-sm">

☁ Cloud Cover

</p>

<h2 class="mt-3 text-3xl font-black text-white">

{{ $environment['weather']['cloud_cover'] }}%

</h2>

</div>

<div class="rounded-2xl border border-cyan-500/30 bg-cyan-500/10 p-5">

<p class="text-cyan-300 text-sm">

🌍 Weather Score

</p>

<h2 class="mt-3 text-3xl font-black text-cyan-300">

{{ $environment['weather_score'] }}

</h2>

</div>

</div>



<hr class="border-slate-800 my-10">


<div class="grid lg:grid-cols-2 gap-8">

<div>

<p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">

AI WEATHER SUMMARY

</p>

<p class="mt-5 text-slate-300 leading-8">

{{ $environment['recommendation'] }}

</p>

</div>

<div class="space-y-6">

<div>

<div class="flex justify-between text-sm mb-2">

<span class="text-slate-400">

Route Health

</span>

<span class="font-bold text-white">

{{ $environment['route_score'] }}%

</span>

</div>

<div class="h-3 rounded-full bg-slate-800 overflow-hidden">

<div
class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-emerald-400 transition-all duration-700"

style="width:{{ $environment['route_score'] }}%">

</div>

</div>

</div>

<div>

<div class="flex justify-between text-sm mb-2">

<span class="text-slate-400">

AI Confidence

</span>

<span class="font-bold text-white">

{{ $environment['confidence'] }}%

</span>

</div>

<div class="h-3 rounded-full bg-slate-800 overflow-hidden">

<div
class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-cyan-400 transition-all duration-700"

style="width:{{ $environment['confidence'] }}%">

</div>

</div>

</div>

<div>

<div class="flex justify-between text-sm mb-2">

<span class="text-slate-400">

Environmental Risk

</span>

<span class="font-bold text-white">

{{ $environment['environmental_risk'] }}%

</span>

</div>

<div class="h-3 rounded-full bg-slate-800 overflow-hidden">

<div
class="h-full rounded-full bg-gradient-to-r from-orange-400 to-red-500 transition-all duration-700"

style="width:{{ $environment['environmental_risk'] }}%">

</div>

</div>

</div>


</div>

</div>

</div>

</div>

</div>

{{-- ========================================= --}}
{{-- WEATHER ANALYTICS --}}
{{-- ========================================= --}}

<section class="mt-14">

<div class="flex justify-between items-end">

    <div>

        <p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">

            LIVE ENVIRONMENT ANALYTICS

        </p>

        <h2 class="mt-2 text-4xl font-black text-white">

            Weather Forecast

        </h2>

    </div>

    <p class="text-slate-500">

        AI Prediction • Next 6 Hours

    </p>
    

</div>

<div class="flex flex-wrap gap-3 mt-8 text-white">

    <button class="weather-tab active" data-type="temp">
        🌡 Temperature
    </button>

    <button class="weather-tab" data-type="humidity">
        💧 Humidity
    </button>

    <button class="weather-tab" data-type="wind">
        🌬 Wind
    </button>

    <button class="weather-tab" data-type="rain">
        🌧 Rain Probability
    </button>

    <button class="weather-tab" data-type="cloud">
        ☁ Cloud
    </button>

</div>

    <div
class="mt-8 h-[430px] rounded-3xl bg-gradient-to-b from-cyan-500/5 to-transparent p-5 w-full">

        <canvas id="temperatureChart"></canvas>

    </div>

</section>
    </div>

</div>

</div>
<br>

<!-- AI Executive Summary -->

<div
class="mb-10
rounded-[2rem]
bg-gradient-to-r
from-indigo-700
via-indigo-600
to-cyan-600
shadow-2xl
overflow-hidden">

<div class="p-8">

<div class="flex flex-col lg:flex-row justify-between gap-8">

<div class="flex-1">

<p class="uppercase tracking-[0.35em] text-xs font-black text-indigo-200">
AI EXECUTIVE SUMMARY
</p>

<h2 class="text-4xl font-black text-white mt-3">
🧠 Today's Logistics Recommendation
</h2>

<p class="text-indigo-100 mt-5 leading-8 text-[15px]">

AI analyzed

<strong>{{ $totalAnalyses }}</strong>

shipment analyses.

<br>

@if($criticalShipments)

🚨
<strong>{{ $criticalShipments }}</strong>

critical shipment(s) require immediate attention.

<br>

@endif

@if($shipImmediately)

🚚
<strong>{{ $shipImmediately }}</strong>

shipment(s) should be dispatched immediately.

<br>

@endif

@if($optimizeRoute)

🛣️
<strong>{{ $optimizeRoute }}</strong>

shipment(s) require route optimization.

<br>

@endif

🌱
Estimated food waste reduction

<strong>{{ $estimatedWasteReduction }}%</strong>

if AI recommendations are followed.

</p>

<div class="mt-8 grid grid-cols-2 gap-4">

    <div class="p-5 rounded-xl bg-white/15 backdrop-blur border border-white/20">
        <p class="text-xs uppercase text-indigo-200">AI Analyses</p>
        <p class="text-3xl font-black text-white mt-1">
            {{ $totalAnalyses }}
        </p>
    </div>

    <div class="p-5 rounded-xl bg-white/15 backdrop-blur border border-white/20">
        <p class="text-xs uppercase text-indigo-200">Sustainability</p>
        <p class="text-3xl font-black text-white mt-1">
            {{ round($avgScore) }}%
        </p>
    </div>

</div>

</div>

<div
class="w-full
lg:w-[360px]
rounded-3xl
bg-white/10
border border-white/20
backdrop-blur-xl
p-6">

<p class="text-xs uppercase tracking-[0.3em] text-indigo-200 font-black">
AI PERFORMANCE
</p>

<div class="grid grid-cols-2 gap-4 mt-6">


    <div class="rounded-2xl bg-white/10 p-4 border border-white/10">
        <p class="text-indigo-200 text-xs uppercase">
            Critical
        </p>

        <p class="text-3xl font-black text-rose-300 mt-2">
            {{ $criticalShipments }}
        </p>
    </div>

    <div class="rounded-2xl bg-white/10 p-4 border border-white/10">
        <p class="text-indigo-200 text-xs uppercase">
            Waste Saved
        </p>

        <p class="text-3xl font-black text-cyan-300 mt-2">
            {{ number_format($totalWaste,0) }}kg
        </p>
    </div>

</div>

<div class="mt-6 border-t border-white/10 pt-5">

<div class="flex justify-between items-center">

<span class="text-indigo-200 text-sm">

AI Engine

</span>

<span class="text-emerald-300 font-bold">

🟢 Online

</span>

</div>

<div class="flex justify-between items-center mt-3">

<span class="text-indigo-200 text-sm">

Decision Engine

</span>

<span class="text-emerald-300 font-bold">

🟢 Active

</span>

</div>

<div class="flex justify-between items-center mt-3">

<span class="text-indigo-200 text-sm">

Route Optimizer

</span>

<span class="text-emerald-300 font-bold">

🟢 Ready

</span>

</div>

<div class="flex justify-between items-center mt-3">

<span class="text-indigo-200 text-sm">

Prediction Model

</span>

<span class="text-emerald-300 font-bold">

🟢 Running

</span>

</div>

</div>

</div>

</div>

</div>

</div>
<section class="mt-10">

    <div class="mb-10">

        <p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">
            AI IMPACT SIMULATION
        </p>

        <h2 class="mt-3 text-5xl font-black text-white">
            Before vs After AI Optimization
        </h2>

        <p class="mt-4 text-slate-400 text-lg max-w-3xl leading-8">
            Estimated improvements generated by AgriFlow AI if all recommendations are implemented.
        </p>

    </div>

    <div class="grid lg:grid-cols-2 xl:grid-cols-4 gap-7">




{{-- ====================================================== --}}
{{-- Operational Risk --}}
{{-- ====================================================== --}}

<div class="rounded-[32px] bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 p-8 hover:border-cyan-500 duration-300">

<p class="uppercase tracking-widest text-slate-400 text-xs font-black">
Operational Risk
</p>

<div class="mt-8 text-center">

<p class="text-slate-500 text-sm">
Current
</p>

<h1 class="text-6xl font-black text-white">
{{ $currentRisk }}%
</h1>

<div class="my-6">

<div class="w-14 h-14 rounded-full bg-emerald-500/20 mx-auto flex items-center justify-center">

<svg class="w-7 h-7 text-emerald-400"
fill="none"
stroke="currentColor"
viewBox="0 0 24 24">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2.5"
d="M19 14l-7 7-7-7m7 7V3"/>

</svg>

</div>

</div>

<p class="text-cyan-300 text-sm">
Optimized
</p>

<h1 class="text-5xl font-black text-emerald-400">
{{ $projectedRisk }}%
</h1>

</div>

<div class="mt-8">

<div class="h-3 rounded-full bg-slate-700 overflow-hidden">

<div
class="h-full bg-gradient-to-r from-red-500 via-orange-400 to-emerald-400 rounded-full"
style="width:75%">
</div>

</div>

<p class="mt-5 text-center font-bold text-emerald-300">
↓ {{ $riskReduction }}% Lower Risk
</p>

</div>

</div>







{{-- ====================================================== --}}
{{-- FOOD WASTE --}}
{{-- ====================================================== --}}

<div class="rounded-[32px] bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 p-8 hover:border-cyan-500 duration-300">

<p class="uppercase tracking-widest text-slate-400 text-xs font-black">
Food Waste
</p>

<div class="mt-8 text-center">

<p class="text-slate-500 text-sm">
Current
</p>

<h1 class="text-5xl font-black text-white">
{{ number_format($currentWaste,0) }}
</h1>

<p class="text-slate-300 font-bold">
kg
</p>

<div class="my-6">

<div class="w-14 h-14 rounded-full bg-cyan-500/20 mx-auto flex items-center justify-center">

<svg class="w-7 h-7 text-cyan-400"
fill="none"
stroke="currentColor"
viewBox="0 0 24 24">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2.5"
d="M19 14l-7 7-7-7m7 7V3"/>

</svg>

</div>

</div>

<p class="text-cyan-300 text-sm">
Optimized
</p>

<h1 class="text-5xl font-black text-cyan-400">
{{ number_format($projectedWaste,0) }}
</h1>

<p class="text-cyan-300 font-bold">
kg
</p>

</div>

<div class="mt-8">

<div class="h-3 rounded-full bg-slate-700 overflow-hidden">

<div
class="h-full bg-gradient-to-r from-amber-400 via-cyan-400 to-sky-500 rounded-full"
style="width:70%">
</div>

</div>

<p class="mt-5 text-center font-bold text-cyan-300">
↓ {{ number_format($wasteSaved,0) }} kg Prevented
</p>

</div>

</div>






{{-- ====================================================== --}}
{{-- CARBON --}}
{{-- ====================================================== --}}

<div class="rounded-[32px] bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 p-8 hover:border-lime-500 duration-300">

<p class="uppercase tracking-widest text-slate-400 text-xs font-black">
Carbon Emission
</p>

<div class="mt-8 text-center">

<p class="text-slate-500 text-sm">
Current
</p>

<h1 class="text-5xl font-black text-white">
{{ number_format($currentCarbon,0) }}
</h1>

<p class="text-slate-300 font-bold">
kg
</p>

<div class="my-6">

<div class="w-14 h-14 rounded-full bg-lime-500/20 mx-auto flex items-center justify-center">

🌱

</div>

</div>

<p class="text-lime-300 text-sm">
Optimized
</p>

<h1 class="text-5xl font-black text-lime-400">
{{ number_format($projectedCarbon,0) }}
</h1>

<p class="text-lime-300 font-bold">
kg
</p>

</div>

<div class="mt-8">

<div class="h-3 rounded-full bg-slate-700 overflow-hidden">

<div
class="h-full bg-gradient-to-r from-emerald-400 to-lime-500 rounded-full"
style="width:82%">
</div>

</div>

<p class="mt-5 text-center font-bold text-lime-300">
↓ {{ number_format($carbonSaved,0) }} kg CO₂ Saved
</p>

</div>

</div>







{{-- ====================================================== --}}
{{-- DELIVERY --}}
{{-- ====================================================== --}}

<div class="rounded-[32px] bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 p-8 hover:border-indigo-500 duration-300">

<p class="uppercase tracking-widest text-slate-400 text-xs font-black">
Delivery Efficiency
</p>

<div class="mt-8 text-center">

<p class="text-slate-500 text-sm">
Current
</p>

<h1 class="text-6xl font-black text-white">
{{ $currentEfficiency }}%
</h1>

<div class="my-6">

<div class="w-14 h-14 rounded-full bg-indigo-500/20 mx-auto flex items-center justify-center">

⚡

</div>

</div>

<p class="text-indigo-300 text-sm">
Optimized
</p>

<h1 class="text-5xl font-black text-indigo-300">
{{ $projectedEfficiency }}%
</h1>

</div>

<div class="mt-8">

<div class="h-3 rounded-full bg-slate-700 overflow-hidden">

<div
class="h-full bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400 rounded-full"
style="width:85%">
</div>

</div>

<p class="mt-5 text-center font-bold text-indigo-300">
↑ {{ $efficiencyGain }}% Faster
</p>

</div>

</div>

</div>

</section>
<br>
        <!-- Main Content -->

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8 animate-card delay-2">

                <div class="bg-gradient-to-br from-indigo-600 to-purple-800 p-8 rounded-3xl text-white shadow-2xl shadow-indigo-500/20">

                    <p class="uppercase tracking-widest text-xs font-bold opacity-80">AI Insight Score</p>

                    <p class="text-6xl font-black mt-2">{{ number_format($avgScore, 1) }}</p>

                    <p class="mt-4 text-indigo-100 text-sm">Aggregated sustainability performance across all tracked harvest systems.</p>

                </div>



                <div class="glass-card p-8 rounded-3xl text-white">

                    <div class="flex items-center justify-between mb-6">

                        <h3 class="text-sm font-black uppercase tracking-widest">Priority Actions</h3>
                            <div class="flex items-center gap-3">

                        <span class="text-[10px] font-bold bg-rose-500/20 text-rose-300 px-3 py-1 rounded-full uppercase">{{ $highRisk }} Critical</span>
<a href="{{ route('ai-analysis.history') }}"
   class="group flex items-center gap-1 text-cyan-300 hover:text-white transition-all duration-300">

    <span class="text-[10px] font-black uppercase tracking-wider">
        View All
    </span>

    <svg class="w-3 h-3 transition-transform duration-300 group-hover:translate-x-1"
         fill="none"
         stroke="currentColor"
         viewBox="0 0 24 24">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"/>
    </svg>

</a>
                            </div>
                    </div>

                        <div class="max-h-80 overflow-y-auto pr-2 custom-scrollbar">

@foreach(\App\Models\AiAnalysis::where('risk_level', 'High')->with('shipment.harvest')->get() as $alert)

                    <a href="{{ route('shipments.show', $alert->shipment_id) }}" class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 mb-3 transition-all">

                        <div>

                            <p class="font-bold text-sm">{{ $alert->shipment->harvest->commodity ?? 'Commodity' }}</p>

                            <p class="text-[10px] text-slate-400">{{ Str::limit($alert->recommendations, 60) }}</p>

                        </div>

                        <span class="text-cyan-400 font-bold text-xs">View →</span>

                    </a>

                    @endforeach
                </div>
                    </div>



                <div class="grid grid-cols-1 md:grid-cols- gap-6">

                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 p-6 rounded-3xl text-white">

                        <p class="text-xs font-bold opacity-80 uppercase">Green Impact Score</p>

                        <p class="text-4xl font-black mt-2">{{ $greenImpactScore }}%</p>

                    </div>

                    <div class="glass-card p-6 rounded-3xl flex flex-col justify-center">

                        <p class="text-xs font-bold text-slate-400 uppercase">Waste Prevented</p>

                        <p class="text-3xl font-black text-white mt-1">{{ number_format($totalWaste, 0, ',', '.') }} KG</p>

                        <div class="w-full bg-slate-700 h-2 rounded-full mt-3 overflow-hidden">

                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $greenImpactScore }}%"></div>

                        </div>

                    </div>

                </div>

                    <div class="glass-card p-6 rounded-3xl">

                        <h2 class="font-bold text-white mb-4">System Insight</h2>

                        <p class="text-slate-400 text-xs italic">"{{ $aiInsightText }}"</p>

                    </div>

                    <div class="glass-card p-6 rounded-3xl relative overflow-hidden">

    <div class="absolute -top-10 -right-10 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl"></div>

    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-[10px] uppercase tracking-[3px] text-cyan-400 font-black">
                AI Executive Summary
            </p>

            <h2 class="text-xl font-black text-white mt-1">
                Logistics Overview
            </h2>
        </div>

        <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 flex items-center justify-center text-xl">
            🤖
        </div>
    </div>

    <div class="space-y-4">

        <div class="flex items-center justify-between">

            <div>
                <p class="text-xs text-slate-400">
                    Critical Shipments
                </p>

                <p class="text-2xl font-black text-rose-400">
                    {{ $criticalShipments }}
                </p>
            </div>

            <div class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center">
                🚨
            </div>

        </div>

        <div class="h-px bg-white/10"></div>

        <div class="flex items-center justify-between">

            <div>

                <p class="text-xs text-slate-400">
                    Route Optimization
                </p>

                <p class="text-lg font-bold text-cyan-300">
                    {{ $optimizeRoute }} Routes
                </p>

            </div>

            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                🛣️
            </div>

        </div>

        <div class="h-px bg-white/10"></div>

        <div class="flex items-center justify-between">

            <div>

                <p class="text-xs text-slate-400">
                    Immediate Dispatch
                </p>

                <p class="text-lg font-bold text-amber-300">
                    {{ $shipImmediately }} Shipments
                </p>

            </div>

            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center">
                🚚
            </div>

        </div>

        <div class="mt-6">

            <div class="flex justify-between mb-2">

                <span class="text-xs text-slate-400">
                    Estimated Waste Reduction
                </span>

                <span class="text-sm font-bold text-emerald-400">
                    {{ $estimatedWasteReduction }}%
                </span>

            </div>

            <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden">

                <div
                    class="h-full rounded-full bg-gradient-to-r from-emerald-400 via-cyan-400 to-indigo-500 transition-all duration-700"
                    style="width: {{ $estimatedWasteReduction }}%">
                </div>

            </div>

        </div>

        <div class="mt-6 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 p-4">

            <p class="text-xs text-cyan-300 uppercase tracking-widest font-bold mb-2">
                AI Verdict
            </p>

            <p class="text-sm text-slate-300 leading-relaxed">

                @if($criticalShipments >= 5)

                    Several shipments require immediate operational attention. Prioritize dispatch scheduling to minimize spoilage and improve logistics efficiency.

                @elseif($criticalShipments >=2)

                    The logistics network remains stable, but several shipments should be monitored to maintain sustainability performance.

                @else

                    Current logistics performance is healthy. Continue monitoring shipment quality and optimize routes where possible.

                @endif

            </p>

        </div>

    </div>

</div>

            </div>



            <div class="space-y-8 animate-card delay-3">

                <div class="glass-card p-6 rounded-3xl">

                    <h2 class="font-bold text-white mb-6">Risk Distribution</h2>

                    <div class="h-64"><canvas id="riskChart"></canvas></div>

                </div>

                <div class="glass-card p-6 rounded-3xl mt-8">

    <div class="flex items-center justify-between mb-5">

        <h2 class="font-bold text-white">
            Shipment Status
        </h2>

        <span class="text-xs text-slate-400 uppercase">
            Live
        </span>

    </div>

    <div class="h-64">

        <canvas id="shipmentStatusChart"></canvas>

    </div>

    <div class="glass-card p-6 rounded-3xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-bold text-white">
            AI Prediction Trend
        </h2>

        <span class="text-xs text-cyan-300">
            Next 7 Days
        </span>
    </div>

    <div class="h-72">
        <canvas id="predictionChart"></canvas>
    </div>
</div>

</div>
                @if($latestHighRisk)

    @php

        $text = $latestHighRisk->recommendations;

        // Memisahkan teks berdasarkan keyword "Explanation:"

        $parts = explode('Explanation:', $text);

        $recommendation = $parts[0] ?? '';

        $explanation = isset($parts[1]) ? 'Explanation:' . $parts[1] : '';

    @endphp



    <div class="bg-rose-950/30 p-6 rounded-3xl border border-rose-900/50">

        <h2 class="font-bold text-rose-400 mb-3 flex items-center gap-2">🚨 Critical Alert</h2>

       

        <div class="space-y-4 text-xs text-rose-200 leading-relaxed">

            <div>

                <p class="font-bold text-rose-300 uppercase tracking-wider mb-1">Recommendations:</p>

                <p>{{ str_replace('Recommendations:', '', $recommendation) }}</p>

            </div>

           

            @if($explanation)

            <div class="pt-3 border-t border-rose-900/50">

                <p class="italic text-rose-400/80">{{ $explanation }}</p>

            </div>

            @endif

        </div>

    </div>

@endif

            </div>

        </div>

    </div>

    <!-- Modal -->

    <div id="myModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4">

        <div class="bg-slate-900 border border-slate-700 rounded-3xl p-8 max-w-sm w-full shadow-2xl">

            <h2 id="modalTitle" class="text-xl font-black text-white mb-4"></h2>

            <div id="modalContent" class="text-slate-300 text-sm"></div>

            <button onclick="closeModal()" class="mt-8 w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-500">Close</button>

        </div>

    </div>

<script>

    function openModal(element) {

        const title = element.getAttribute('data-title');

        const content = element.getAttribute('data-content');

       

        document.getElementById('modalTitle').innerText = title;

        // Kita decode kembali supaya tag HTML (<ul>, <li>, <strong>) balik lagi

        document.getElementById('modalContent').innerHTML = content;

        document.getElementById('myModal').style.display = 'flex';

    }



    function closeModal() {

        document.getElementById('myModal').style.display = 'none';

    }



    // Klik di luar modal buat nutup

    window.onclick = function(event) {

        const modal = document.getElementById('myModal');

        if (event.target == modal) {

            closeModal();

        }

    }

</script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>

    const ctx = document.getElementById('riskChart').getContext('2d');

    // Register plugin secara global atau lokal

    Chart.register(ChartDataLabels);

    new Chart(ctx, {

        type: 'doughnut',

        data: {

            labels: ['Low', 'Medium', 'High'],

            datasets: [{

                data: [{{ $lowRisk }}, {{ $mediumRisk }}, {{ $highRisk }}],

                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],

                borderWidth: 0

            }]

        },

options: {

    responsive: true,

    maintainAspectRatio: false,

    plugins: {

            legend: {
        position: 'bottom',
        labels: {
            color: '#fff'
        }
    },

        // 1. Datalabels: Untuk angka permanen di chart

        datalabels: {

            color: '#fff',

            font: { weight: 'bold' },

            formatter: (value, ctx) => {

                if (value === 0) return ""; // Sembunyiin kalau 0

                let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);

                return ((value * 100) / sum).toFixed(0) + "%";

            }

        },

        // 2. Tooltip: Untuk detail saat di-hover

        tooltip: {

            callbacks: {

                label: function(context) {

                    let value = context.raw;

                    let sum = context.dataset.data.reduce((a, b) => a + b, 0);

                    let percentage = ((value / sum) * 100).toFixed(1);

                    return ` ${context.label}: ${value} (${percentage}%)`;

                }

            }

        }

    }

}

    });

</script>

<script>

const shipmentCtx = document.getElementById('shipmentStatusChart');

new Chart(shipmentCtx, {

    type: 'bar',

    data: {

        labels: [
            'Harvested',
            'Packed',
            'In Transit',
            'Delivered'
        ],

        datasets: [{

            data: [
                {{ $statusHarvested }},
                {{ $statusPacked }},
                {{ $statusTransit }},
                {{ $statusDelivered }}
            ],

            borderRadius: 12,

            backgroundColor: [
                '#6366f1',
                '#f59e0b',
                '#06b6d4',
                '#10b981'
            ]

        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: false
            },

            datalabels: {

                color: '#fff',

anchor: 'center',
align: 'center',

                font: {
                    size: 14,
                    weight: 'bold'
                },

                formatter: function(value){
                    return value;
                }

            }

        },

        scales: {

            x: {

                ticks: {
                    color: '#fff'
                },

                grid: {
                    display: false
                }

            },

            y: {

                beginAtZero: true,

                ticks: {
                    color: '#fff'
                },

                grid: {
                    color: 'rgba(255,255,255,.08)'
                }

            }

        }

    },

    plugins: [ChartDataLabels]

});

const predictionCtx =
document.getElementById('predictionChart');

new Chart(predictionCtx,{

type:'line',

data:{

labels:[
'Day 1',
'Day 2',
'Day 3',
'Day 4',
'Day 5',
'Day 6',
'Day 7'
],

datasets:[{

label:'Predicted Risk',

data:@json($predictionTrend),

borderColor:'#06b6d4',

backgroundColor:'rgba(6,182,212,.15)',

fill:true,

tension:.4,

pointRadius:5,

pointHoverRadius:7

}]

},

options:{

responsive:true,

maintainAspectRatio:false,

plugins:{

legend:{
labels:{
color:'#fff'
}
}

},

scales:{

x:{
ticks:{
color:'#fff'
},
grid:{
color:'rgba(255,255,255,.08)'
}
},

y:{
beginAtZero:true,
max:100,
ticks:{
color:'#fff'
},
grid:{
color:'rgba(255,255,255,.08)'
}
}

}

}

});

</script>
<script>

document.addEventListener("DOMContentLoaded",()=>{

const ctx=document.getElementById("temperatureChart");

if(!ctx)return;

const weatherSeries={

temp:@json(collect($weatherTrend)->pluck('temp')),

humidity:@json(collect($weatherTrend)->pluck('humidity')),

wind:@json(collect($weatherTrend)->pluck('wind')),

rain:@json(collect($weatherTrend)->pluck('rain')),

cloud:@json(collect($weatherTrend)->pluck('cloud'))

};

const weatherColor={

temp:"#22d3ee",

humidity:"#38bdf8",

wind:"#22c55e",

rain:"#6366f1",

cloud:"#94a3b8"

};

const gradientColor={

temp:"rgba(34,211,238,.45)",

humidity:"rgba(56,189,248,.45)",

wind:"rgba(34,197,94,.45)",

rain:"rgba(99,102,241,.45)",

cloud:"rgba(148,163,184,.45)"

};

const weatherChart=new Chart(ctx,{

type:"line",

data:{

labels:@json(collect($weatherTrend)->pluck('time')),

datasets:[{

label:"Temperature",

data:weatherSeries.temp,

borderColor:"#22d3ee",

borderWidth:5,

fill:true,

tension:.45,

pointRadius:0,

pointHoverRadius:8,

backgroundColor:(ctx)=>{

const chart=ctx.chart;

const {ctx:canvas,chartArea}=chart;

if(!chartArea)return null;

const gradient=canvas.createLinearGradient(
0,
chartArea.top,
0,
chartArea.bottom
);

const activeType="temp";

gradient.addColorStop(
    0,
    gradientColor[activeType]
);

gradient.addColorStop(
    1,
    gradientColor[activeType].replace(".45","0")
);

return gradient;

}

}]

},

options:{

responsive:true,

maintainAspectRatio:false,

interaction:{
mode:"index",
intersect:false
},

plugins: {

    legend:{
        display:false
    },

    tooltip:{

        callbacks:{

            label:function(context){

                const label = context.dataset.label;

                const value = context.parsed.y;

                if(label === "Rain Probability"){

                    return label + ": " + value + "%";

                }

                if(label === "Humidity"){

                    return label + ": " + value + "%";

                }

                if(label === "Cloud Cover"){

                    return label + ": " + value + "%";

                }

                if(label === "Temperature"){

                    return label + ": " + value + "°C";

                }

                if(label === "Wind Speed"){

                    return label + ": " + value + " km/h";

                }

                return label + ": " + value;

            }

        }

    }

},

animations:{

tension:{

duration:1800,

easing:"easeInOutQuart"

}

},

scales:{

x:{

grid:{
display:false
},

ticks:{
color:"#94a3b8"
}

},

y:{

grid:{

color:"rgba(255,255,255,.05)",

drawBorder:false

},

ticks:{

color:"#94a3b8"

}

}

}

}

    });

    // ============================
    // Weather Tabs
    // ============================

    document.querySelectorAll(".weather-tab").forEach(tab=>{

        tab.addEventListener("click",()=>{

            document.querySelectorAll(".weather-tab").forEach(btn=>{

                btn.classList.remove("active");

            });

            tab.classList.add("active");

            const type=tab.dataset.type;

const labels = {

    temp: "Temperature",

    humidity: "Humidity",

    wind: "Wind Speed",

    rain: "Rain Probability",

    cloud: "Cloud Cover"

};

            weatherChart.data.datasets[0].label=labels[type];

            weatherChart.data.datasets[0].data=weatherSeries[type];

            weatherChart.data.datasets[0].borderColor=weatherColor[type];

            weatherChart.update('active');

        });

    });

});

</script>

</x-app-layout>

