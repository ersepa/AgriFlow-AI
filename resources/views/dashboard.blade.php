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

                $items = [

                    ['Total Harvest', $totalHarvests, 'text-white'],

                    ['Total Weight', number_format($totalWeight, 0, ',', '.') . ' KG', 'text-white'],

                    ['Shipments', $totalShipments, 'text-white'],

                    ['Delivered', $deliveredShipments, 'text-emerald-400'],

                    ['AI Analyses', $totalAnalyses, 'text-indigo-400'],

                    ['High Risk', $highRisk, 'text-rose-400']

                ];

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

</x-app-layout>

