<x-app-layout>
    <style>
        /* Fix Leaflet overlay behind modal */

.leaflet-container {
    z-index: 1 !important;
}

.leaflet-pane,
.leaflet-control {
    z-index: 1 !important;
}


#aiModal {
    z-index: 9999 !important;
}
    </style>
    <div class="min-h-screen bg-slate-950 p-4 md:p-8"
         x-data="{ loaded: false }" 
         x-init="setTimeout(() => loaded = true, 500)">

        <div class="max-w-7xl mx-auto space-y-8">

            {{-- HERO SECTION --}}
            <section class="relative rounded-[2.5rem] p-8 md:p-12 overflow-hidden bg-slate-900 border border-white/10 shadow-2xl">
                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-purple-600/20"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-[10px] font-black tracking-widest uppercase">
                                AI-Powered Logistics
                            </span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter leading-none">
                            Shipment <span class="text-blue-400">Optimizer</span>
                        </h1>
                        <p class="mt-4 text-slate-400 text-lg max-w-lg font-medium">
                            Monitoring logistik cerdas dengan prediksi risiko dan optimasi keberlanjutan berbasis data real-time.
                        </p>
                    </div>
                </div>
            </section>

            {{-- SUMMARY CARDS --}}
            <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([['Total', $totalShipments, '📦', 'from-blue-500'], ['Critical', $criticalCount, '🚨', 'from-red-500'], ['Risk', $highCount, '⚠️', 'from-amber-500'], ['Eco', $averageSustainability . '%', '🌱', 'from-emerald-500']] as $stat)
                <div class="bg-slate-900 border border-white/5 p-6 rounded-[2rem] hover:bg-slate-800 transition-all duration-300">
                    <div class="text-2xl mb-3">{{ $stat[2] }}</div>
                    <p class="text-[10px] font-bold text-white uppercase tracking-widest">{{ $stat[0] }}</p>
                    <h2 class="text-3xl font-black text-white mt-1">{{ $stat[1] }}</h2>
                </div>
                @endforeach
            </section>

{{-- MAP SECTION --}}
<section class="rounded-[2.5rem]
                bg-gradient-to-br
                from-slate-900
                to-slate-800
                border
                border-white/10
                p-6
                shadow-2xl">

    {{-- HEADER MAP --}}
    <div class="flex justify-between items-center mb-5">

        <div>

            <h2 class="text-2xl font-black text-white">
                🗺 Live Shipment Route
            </h2>

            <p class="text-slate-400">
                AI Navigation & Logistics Visualization
            </p>

        </div>

<div class="flex gap-3">

<span class="px-4 py-2 rounded-full
bg-blue-500/20
text-blue-300
text-xs
font-bold">

🚚 {{ $totalShipments }} Shipments

</span>

<span class="px-4 py-2 rounded-full
bg-emerald-500/20
text-emerald-300
text-xs
font-bold animate-pulse">

🟢 LIVE

</span>

</div>

    </div>


    {{-- MAP --}}
    <div class="rounded-[2rem] overflow-hidden border border-white/10">

        <div id="map" class="h-[520px] w-full"></div>

    </div>

</section>

            {{-- LIST SECTION --}}
<section class="space-y-6">
    @foreach($results as $index => $result)
<div
    x-data="{ show: false }"
    x-init="setTimeout(() => show = true, {{ $index * 150 }})"
class="shipment-card
relative
overflow-hidden
rounded-[2.5rem]
p-8

bg-gradient-to-br
from-[#0F172A]
via-[#162033]
to-[#111827]

border
border-white/10

shadow-2xl
shadow-black/50

hover:border-cyan-400/60
hover:shadow-cyan-500/20

transition-all
duration-500"
    data-origin-lat="{{ $result['origin_lat'] }}"
    data-origin-lng="{{ $result['origin_lng'] }}"
    data-dest-lat="{{ $result['destination_lat'] }}"
    data-dest-lng="{{ $result['destination_lng'] }}">
     <span class="hidden">DEBUG: {{ $result['origin_lat'] }}</span>
     <div class="absolute -top-24 -right-24 w-52 h-52 bg-blue-400/10 rounded-full blur-3xl"></div>

<div class="absolute -bottom-24 -left-24 w-44 h-44 bg-emerald-400/10 rounded-full blur-3xl"></div>
     <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-cyan-400 to-emerald-400"></div>
         
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-8">
            <div>
                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">Priority #{{ $index + 1 }}</span>
                <h2 class="text-2xl font-black text-white mt-2">{{ ucfirst($result['commodity']) }}</h2>
                <p class="text-slate-400 font-medium text-sm">{{ $result['origin'] }} ➔ {{ $result['destination'] }}</p>
            </div>
<div class="flex items-center gap-3">

<button 
    onclick="updateMap(this.closest('.shipment-card'), this)"
    class="route-btn px-5 py-2 rounded-full bg-blue-600 text-white text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30">

    <span class="route-text">
        🚚 Route
    </span>

</button>
    <button
onclick="showAIExplain({{ $result['shipment']->id }})"
class="px-5 py-2 rounded-full 
bg-purple-600 
text-white 
text-xs 
font-black 
uppercase
hover:bg-purple-700
transition">

🧠 AI Explain

</button>


    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">
        {{ $result['priority_level'] }}
    </span>

</div>
        </div>

<div class="grid md:grid-cols-4 gap-8 text-white">
@php
$metrics = [
    ['Priority', $result['priority_score'], 'bg-blue-500'],
    ['Risk', $result['risk_score'], 'bg-red-500'],
    ['Sustainability', $result['sustainability_score'], 'bg-emerald-500'],
    ['Efficiency', $result['efficiency_score'], 'bg-indigo-500'],
];
@endphp

@foreach($metrics as $metric)

    <div
        x-data="{ width: 0 }"
        x-intersect.once="setTimeout(() => width = {{ $metric[1] }}, 100)"
    >

        <div class="flex justify-between mb-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase">
                {{ $metric[0] }}
            </span>

            <span class="text-sm font-black">
                {{ $metric[1] }}%
            </span>
        </div>

<div class="h-2.5 bg-slate-100 border border-slate-200 rounded-full overflow-hidden">

<div
    class="h-full {{ $metric[2] }} rounded-full shadow-lg"
    :style="`
        width:${width}%;
        transition:width 1.8s cubic-bezier(.4,0,.2,1);
    `">
</div>

</div>

    </div>

    @endforeach
</div>
<div class="grid lg:grid-cols-2 gap-6 mt-8">

    {{-- AI Recommendation --}}
    <div class="rounded-3xl bg-gradient-to-br from-blue-600 to-cyan-500 p-6 text-white shadow-xl">

        <div class="flex items-center justify-between mb-4">
            <h3 class="font-black text-lg">
                🤖 AI Recommendation
            </h3>

            <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-bold">
                AI Decision
            </span>
        </div>

        <p class="text-blue-50 leading-relaxed">
            Click "AI Explain" to generate an AI recommendation.
        </p>

    </div>



    {{-- Decision Factors --}}
    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6">

        <h3 class="font-black text-white text-lg mb-4">
            📌 Decision Factors
        </h3>

        <p class="text-slate-300 leading-relaxed">
            Click "AI Explain" to generate an Decision Factors analysis.
        </p>

    </div>

</div>
    </div>
    @endforeach
    {{-- PAGINATION --}}
    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">
    Page {{ $results->currentPage() }} 
    of 
    {{ $results->lastPage() }}
</p>
<div class="mt-10 flex flex-col items-center gap-4">

    <p class="text-sm text-slate-400 font-medium">
        Showing 
        <span class="text-white font-black">
            {{ $results->firstItem() }}
        </span>

        -

        <span class="text-white font-black">
            {{ $results->lastItem() }}
        </span>

        of

        <span class="text-blue-400 font-black">
            {{ $results->total() }}
        </span>

        shipments
    </p>


    {{ $results->links() }}

</div>

</section>
        </div>
    </div>
<div id="aiModal"
class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] flex items-center justify-center p-5 pointer-events-auto">


<div class="bg-slate-900 rounded-[2rem] 
border border-white/10 
max-w-xl w-full p-8 text-white">


<div class="flex justify-between items-center mb-6">

<h2 class="text-2xl font-black">
🧠 AI Explainability
</h2>

<div id="aiLoading" class="py-10 text-center">

<p class="text-slate-400 text-sm font-bold mb-5">
    Analyzing shipment...
</p>


<div class="flex justify-center mb-5">

    <div class="w-12 h-12 border-4 border-blue-500/30 border-t-blue-400 rounded-full animate-spin">
    </div>

</div>


<p class="text-slate-300 font-bold">
    AI is analyzing shipment data...
</p>


<p class="text-slate-500 text-sm mt-2">
    Evaluating risk, route, and sustainability factors
</p>


</div>


<button 
onclick="closeAIExplain()"
class="text-slate-400 hover:text-white text-xl">
    ✕
</button>

</div>


<div id="aiResult" class="hidden space-y-5">


<div>
<p class="text-xs text-slate-400 uppercase">
AI Recommendation
</p>

<p id="aiRecommendation"
class="font-bold mt-2">
-
</p>
</div>



<div>
<p class="text-xs text-slate-400 uppercase">
Decision Factors
</p>

<p id="aiReason"
class="mt-2">
-
</p>
</div>



<div>
<p class="text-xs text-slate-400 uppercase">
AI Conclusion
</p>

<p id="aiConclusion"
class="mt-2">
-
</p>
</div>



<div>
</div>


</div>


</div>

</div>
</x-app-layout>

<link 
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<style>

.leaflet-popup-content-wrapper{
    border-radius:20px;
    padding:5px;
}

.leaflet-popup-tip{
    display:none;
}

.leaflet-control-zoom{
    border:none!important;
}

.leaflet-control-zoom a{
    border-radius:12px!important;
    margin-bottom:5px;
}


.route-marker{

width:45px;
height:45px;
border-radius:50%;

display:flex;
align-items:center;
justify-content:center;

font-size:22px;

box-shadow:
0 10px 30px rgba(0,0,0,.25);

border:3px solid white;

}


</style>



<script>
let map;
let markerOrigin;
let markerDestination;
let truck;
let routeLine;
let animationId = null;

document.addEventListener("DOMContentLoaded",()=>{

map=L.map('map',{
    zoomControl:false,
    attributionControl:false
}).setView([-6.2,106.8],10)


L.tileLayer(
'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
{
    maxZoom:19
}
).addTo(map)


L.control.zoom({
    position:'bottomright'
}).addTo(map)


markerOrigin = L.marker([0,0], {
    icon:createMarker("🌱","#10b981")
}).addTo(map);


markerDestination = L.marker([0,0], {
    icon:createMarker("🏭","#ef4444")
}).addTo(map);


truck = L.marker([0,0], {
    icon:createMarker("🚚","#2563eb")
}).addTo(map);


routeLine = L.polyline([], {
    color:'#2563eb',
    weight:2,
    opacity:0.9,
    lineCap:'round',
    lineJoin:'round',
    dashArray:'18 12'
}).addTo(map);

// Animasi garis route
let dashOffset = 0;

setInterval(() => {
    dashOffset--;

    routeLine.setStyle({
        dashOffset: dashOffset
    });

}, 40);



const firstCard=document.querySelector('.shipment-card');

if(firstCard){
    updateMap(firstCard);
}

});

    // 3. Fungsi utama untuk update map
async function updateMap(element, button=null){

    try{

        if(button){
            setRouteLoading(button,true);
        }


        const oLat = parseFloat(element.getAttribute('data-origin-lat'));
        const oLng = parseFloat(element.getAttribute('data-origin-lng'));
        const dLat = parseFloat(element.getAttribute('data-dest-lat'));
        const dLng = parseFloat(element.getAttribute('data-dest-lng'));


        const newRoute = await getRoadRoute(
            [oLat,oLng],
            [dLat,dLng]
        );


        markerOrigin.setLatLng([oLat,oLng]);
        markerDestination.setLatLng([dLat,dLng]);

        routeLine.setLatLngs(newRoute);

        map.fitBounds(routeLine.getBounds(),{
            padding:[50,50]
        });

        resetTruck(newRoute);


    }catch(error){

        console.error(error);
        alert("Failed to load route");

    }finally{

        if(button){
            setRouteLoading(button,false);
        }

    }

}

    // Fungsi tambahan untuk reset animasi truk

function resetTruck(route) {

    if (animationId) {
        cancelAnimationFrame(animationId);
    }

    if (!route || route.length < 2) return;

    let segment = 0;
    let progress = 0;
    const speed = 200;

    function animate() {

        if (segment >= route.length - 1) {
            segment = 0;
        }

        const start = route[segment];
        const end = route[segment + 1];

        progress += speed;

        if (progress >= 1) {
            progress = 0;
            segment++;
        }

        const lat =
            start[0] + (end[0] - start[0]) * progress;

        const lng =
            start[1] + (end[1] - start[1]) * progress;

        truck.setLatLng([lat, lng]);

        animationId = requestAnimationFrame(animate);
    }

    truck.setLatLng(route[0]);

    animationId = requestAnimationFrame(animate);
}

    // Helper createMarker (tetap sama)
    function createMarker(icon, color) {
        return L.divIcon({ className: "", html: `<div class="route-marker" style="background:${color}">${icon}</div>` });
    }

    async function getRoadRoute(start, end){

    const apiKey = "eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImExYWI1YTUzOTE5MjQ4OTY4NzViYTEwN2M4YjgzMjQ3IiwiaCI6Im11cm11cjY0In0=";

    const response = await fetch(
        `https://api.openrouteservice.org/v2/directions/driving-car/geojson`,
        {
            method:"POST",
            headers:{
                "Authorization":apiKey,
                "Content-Type":"application/json"
            },
            body:JSON.stringify({
                coordinates:[
                    [start[1],start[0]],
                    [end[1],end[0]]
                ]
            })
        }
    );


const data = await response.json();

console.log("AI Explain:", data);

console.log("ORS Response:", data);

return data.features[0].geometry.coordinates.map(c => [
    c[1],
    c[0]
]); 

}
async function showAIExplain(id)
{

    // buka modal dulu
    document
    .getElementById('aiModal')
    .classList
    .remove('hidden');


    // tampilkan loading
    document.getElementById('aiLoading')
    .classList
    .remove('hidden');


    document.getElementById('aiResult')
    .classList
    .add('hidden');


    const response = await fetch(
        `/ai-optimizer/explain/${id}`
    );


    const data = await response.json();



    document.getElementById('aiRecommendation').innerText =
        data.recommendation ?? '-';


    document.getElementById('aiReason').innerText =
        data.decision_reason ?? '-';


    document.getElementById('aiConclusion').innerText =
        data.conclusion ?? '-';
    // hide loading
    document.getElementById('aiLoading')
    .classList
    .add('hidden');


    // tampilkan hasil
    document.getElementById('aiResult')
    .classList
    .remove('hidden');

}

function closeAIExplain()
{
    document
        .getElementById('aiModal')
        .classList
        .add('hidden');

        document
.getElementById('aiModal')
.addEventListener('click', function(e){

    if(e.target === this){
        this.classList.add('hidden');
    }

});
}
function setRouteLoading(button, loading){

    const text = button.querySelector('.route-text');

    if(loading){

        text.innerHTML = `
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                Loading...
            </span>
        `;

        button.disabled = true;
        button.classList.add('opacity-70');

    }else{

        text.innerHTML = "🚚 Route";

        button.disabled = false;
        button.classList.remove('opacity-70');

    }

}
</script>