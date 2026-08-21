<x-app-layout>

<style>
    @keyframes floatCard {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-8px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-float {
        animation: floatCard 5s ease-in-out infinite;
    }

    .fade-up {
        animation: fadeUp .8s ease forwards;
    }

    .glass {
        backdrop-filter: blur(20px);
        background: rgba(255,255,255,.75);
    }

    @keyframes gridMove{

from{

background-position:0 0,0 0;

}

to{

background-position:42px 42px,42px 42px;

}

}

.grid-bg{

animation:gridMove 5s linear infinite;

}

.shipment-active{
    border-color:#06b6d4;
    background:linear-gradient(135deg,#ecfeff,#f8fdff);
    box-shadow:
        0 0 0 1px rgba(6,182,212,.25),
        0 20px 50px rgba(6,182,212,.18);
}

@keyframes fade{

from{

opacity:0;
transform:translateY(15px);

}

to{

opacity:1;
transform:translateY(0);

}

}

.shipment-card{

transition:all .35s cubic-bezier(.22,.61,.36,1);

}

.shipment-card:hover{

transform:translateY(-6px);

}

.shipment-active{

border-color:rgba(34,211,238,.8);

background:linear-gradient(
135deg,
rgb(15 23 42),
rgb(8 47 73),
rgb(15 23 42)
);

box-shadow:
0 0 0 1px rgba(34,211,238,.25),
0 0 50px rgba(34,211,238,.18);

}

#shipmentList::-webkit-scrollbar{

width:8px;

}

#shipmentList::-webkit-scrollbar-thumb{

background:#cbd5e1;
border-radius:999px;

}

#shipmentList::-webkit-scrollbar-thumb:hover{

background:#06b6d4;

}

</style>

<div
    class="relative overflow-hidden rounded-2xl md:rounded-[32px] bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950 border border-cyan-500/20 shadow-[0_0_80px_rgba(34,211,238,.15)]">

    <!-- Background Glow -->
    <div class="absolute inset-0 opacity-20">

        <div
            class="absolute -top-32 -left-24 md:-top-40 md:-left-32 w-72 h-72 md:w-[500px] md:h-[500px] rounded-full bg-cyan-500 blur-[100px] md:blur-[150px]">
        </div>

        <div
            class="absolute bottom-0 right-0 w-64 h-64 md:w-[450px] md:h-[450px] rounded-full bg-indigo-600 blur-[100px] md:blur-[150px]">
        </div>

    </div>

    <!-- Grid -->
    <div class="absolute inset-0">
        <div
            class="h-full w-full bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:32px_32px] md:bg-[size:42px_42px]">
        </div>
    </div>

    <div class="relative px-6 py-10 md:px-10 md:py-14 lg:px-16 lg:py-20">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">

            <div
                class="w-14 h-14 md:w-16 md:h-16 rounded-2xl md:rounded-3xl bg-cyan-500/20 border border-cyan-400/30 flex items-center justify-center text-2xl md:text-3xl">
                🤖
            </div>

            <div>

                <p
                    class="uppercase tracking-[0.3em] md:tracking-[0.45em] text-cyan-400 text-[10px] md:text-xs font-black">
                    AI DIGITAL TWIN
                </p>

                <h1
                    class="mt-2 md:mt-3 text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight">

                    Logistics

                    <span class="text-cyan-400">
                        Simulation
                    </span>

                </h1>

            </div>

        </div>

        <!-- Description -->
        <p
            class="mt-6 md:mt-8 max-w-3xl text-slate-300 text-base md:text-lg lg:text-xl leading-7 md:leading-9">

            Build a virtual replica of your shipment and simulate future logistics conditions before they happen.
            Compare operational strategies, estimate spoilage risk, optimize sustainability, and generate AI-powered
            decisions in real time.

        </p>

        <!-- Cards -->
        <div class="mt-8 md:mt-10 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">

            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur">

                <p class="text-slate-400 text-xs uppercase font-bold">
                    AI Engine
                </p>

                <h2 class="mt-2 text-white font-black text-xl lg:text-2xl">
                    Neural Decision Engine
                </h2>

            </div>

            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur">

                <p class="text-slate-400 text-xs uppercase font-bold">
                    Simulation
                </p>

                <h2 class="mt-2 text-cyan-400 font-black text-xl lg:text-2xl">
                    Real-Time Digital Twin
                </h2>

            </div>

            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur">

                <p class="text-slate-400 text-xs uppercase font-bold">
                    Optimization
                </p>

                <h2 class="mt-2 text-emerald-400 font-black text-xl lg:text-2xl">
                    AI Scenario Analysis
                </h2>

            </div>

        </div>

    </div>

</div>
<div class="mt-10 grid lg:grid-cols-3 gap-8">

{{-- LEFT --}}
<div class="lg:col-span-1">

    <div class="rounded-[30px] border border-slate-200 bg-white shadow-xl overflow-hidden">

        {{-- Header --}}
<div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-cyan-900 to-slate-900 px-8 py-8">

    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,.18),transparent_60%)]"></div>

    <div class="relative">

        <p class="uppercase tracking-[0.4em] text-cyan-300 text-xs font-black">

            Mission Control

        </p>

        <h2 class="mt-3 text-4xl font-black text-white">

            Shipment Database

        </h2>

        <p class="mt-3 text-cyan-100">

            Select one shipment to initialize Digital Twin.

        </p>

    </div>

</div>

        <div class="mt-8 relative">

    <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 11-14 0a7 7 0 0114 0z"/>

    </svg>

<input
id="searchShipment"
type="text"
placeholder="Search shipment..."
class="w-full rounded-2xl
bg-slate-900
border border-slate-700
text-white
placeholder:text-slate-500
px-5 py-4
font-semibold
outline-none
focus:border-cyan-400
focus:ring-4
focus:ring-cyan-500/20">

</div>

<div class="mt-5 flex items-center justify-between">

    <p class="text-sm text-slate-500">

        Available Shipments

    </p>

    <div
        class="rounded-full bg-cyan-100 text-cyan-700 px-3 py-1 text-sm font-black">

        {{ $shipments->count() }}

    </div>

</div>

        {{-- List --}}
        <div
            id="shipmentList"
            class="max-h-[620px] overflow-y-auto p-4 space-y-4">

@foreach($shipments as $shipment)

<button
    type="button"
    class="shipment-card group relative w-full overflow-hidden rounded-[28px]
    border border-cyan-500/10
    bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800
    p-6 text-left
    transition-all duration-300
    hover:-translate-y-1
    hover:border-cyan-400/60
    hover:shadow-[0_0_40px_rgba(34,211,238,.18)]"

    data-id="{{ $shipment->id }}"
    data-commodity="{{ $shipment->harvest->commodity }}"
    data-origin="{{ $shipment->origin }}"
    data-destination="{{ $shipment->destination }}"
    data-status="{{ $shipment->status }}"
    data-distance="{{ round($shipment->distance_km) }}">

    {{-- Glow --}}
    <div
        class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl group-hover:bg-cyan-400/20 transition">
    </div>

    {{-- Top --}}
    <div class="relative flex items-start justify-between">

        <div class="flex items-center gap-4">

            <div
                class="flex h-14 w-14 items-center justify-center rounded-2xl
                bg-cyan-500/15
                border border-cyan-400/20">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7 text-cyan-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 7L12 3L4 7L12 11L20 7ZM4 7V17L12 21V11M20 7V17L12 21"/>

                </svg>

            </div>

            <div>

                <h3 class="text-xl font-black text-white">

                    {{ $shipment->harvest->commodity }}

                </h3>

                <p class="mt-1 text-sm text-slate-400">

                    Shipment #{{ $shipment->id }}

                </p>

            </div>

        </div>

        @php
            $statusColor = match($shipment->status){
                'Delivered' => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/20',
                'In Transit' => 'bg-cyan-500/15 text-cyan-300 border-cyan-400/20',
                'Packed' => 'bg-orange-500/15 text-orange-300 border-orange-400/20',
                default => 'bg-slate-700 text-slate-300 border-slate-600'
            };
        @endphp

        <span
            class="rounded-full border px-4 py-2 text-xs font-black uppercase {{ $statusColor }}">

            {{ $shipment->status }}

        </span>

    </div>

    {{-- Route --}}
    <div class="relative mt-8">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-xs uppercase tracking-widest text-slate-500">

                    Origin

                </p>

                <h4 class="mt-2 text-lg font-bold text-white">

                    {{ $shipment->origin }}

                </h4>

            </div>

            <div
                class="mx-6 flex-1 border-t border-dashed border-cyan-400/30">
            </div>

            <div class="text-right">

                <p class="text-xs uppercase tracking-widest text-slate-500">

                    Destination

                </p>

                <h4 class="mt-2 text-lg font-bold text-white">

                    {{ $shipment->destination }}

                </h4>

            </div>

        </div>

    </div>

    {{-- Bottom --}}
    <div class="relative mt-8 flex items-center justify-between">

        <div>

            <p class="text-xs uppercase tracking-widest text-slate-500">

                Distance

            </p>

            <h2 class="mt-1 text-2xl font-black text-cyan-300">

                {{ round($shipment->distance_km) }}

                <span class="text-lg">km</span>

            </h2>

        </div>

        <div
            class="flex h-12 w-12 items-center justify-center rounded-full
            border border-cyan-400/20
            bg-cyan-500/10
            transition-all
            group-hover:translate-x-1
            group-hover:bg-cyan-500/20">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-cyan-300"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5L16 12L9 19"/>

            </svg>

        </div>

    </div>

</button>

@endforeach
<input type="hidden" id="selectedShipment" value="">
<div class="flex items-center justify-between mt-6">

    <div class="text-sm text-slate-500">

        Showing

        {{ $shipments->firstItem() }}

        -

        {{ $shipments->lastItem() }}

        of

        {{ $shipments->total() }}

        Shipments

    </div>

    <div class="flex gap-3">

        @if($shipments->onFirstPage())

            <button
                disabled
                class="px-5 py-2 rounded-xl bg-slate-100 text-slate-400">

                Previous

            </button>

        @else

            <a
                href="{{ $shipments->previousPageUrl() }}"
                class="px-5 py-2 rounded-xl border hover:border-cyan-500">

                Previous

            </a>

        @endif

        @if($shipments->hasMorePages())

            <a
                href="{{ $shipments->nextPageUrl() }}"
                class="px-5 py-2 rounded-xl bg-cyan-500 text-white hover:bg-cyan-600">

                Next

            </a>

        @else

            <button
                disabled
                class="px-5 py-2 rounded-xl bg-slate-100 text-slate-400">

                Next

            </button>

        @endif

    </div>

</div>
        </div>



    </div>

</div>

{{-- RIGHT --}}
<div class="lg:col-span-2">

    <div
        id="shipmentPreview"
        class="hidden overflow-hidden rounded-[36px]
        border border-cyan-500/20
        bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950
        shadow-[0_0_80px_rgba(34,211,238,.12)]">

        {{-- Header --}}
        <div class="relative overflow-hidden border-b border-cyan-500/10 px-10 py-8">

            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,.15),transparent_55%)]"></div>

            <div class="relative flex items-start justify-between">

                <div>

                    <p class="uppercase tracking-[0.45em] text-cyan-400 text-xs font-black">

                        Digital Twin

                    </p>

                    <h1
                        id="commodity"
                        class="mt-3 text-5xl font-black text-white">

                    </h1>

                    <p class="mt-2 text-slate-400">

                        Logistics Command Center

                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <span class="relative flex h-3 w-3">

                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>

                        <span class="relative inline-flex h-3 w-3 rounded-full bg-cyan-400"></span>

                    </span>

                    <span
                        class="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-5 py-2 text-sm font-black uppercase text-cyan-300">

                        LIVE

                    </span>

                </div>

            </div>

        </div>

        {{-- Route --}}
        <div class="px-10 pt-10">

            <p class="uppercase tracking-[0.3em] text-xs font-black text-slate-500">

                Shipment Route

            </p>

            <div class="mt-8 flex items-center justify-between">

                <div>

                    <p class="text-xs uppercase text-slate-500">

                        Origin

                    </p>

                    <h2
                        id="origin"
                        class="mt-2 text-3xl font-black text-white">

                    </h2>

                </div>

                <div class="mx-10 flex-1">

                    <div class="relative">

                        <div class="border-t border-dashed border-cyan-400/40"></div>

                        <div class="absolute -top-2 left-1/2 -translate-x-1/2">

                            🚚

                        </div>

                    </div>

                </div>

                <div class="text-right">

                    <p class="text-xs uppercase text-slate-500">

                        Destination

                    </p>

                    <h2
                        id="destination"
                        class="mt-2 text-3xl font-black text-white">

                    </h2>

                </div>

            </div>

        </div>

        {{-- Metrics --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-6 px-10 py-10">

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">

                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">

                    Distance

                </p>

                <h2
                    id="distance"
                    class="mt-3 text-3xl font-black text-cyan-300">

                </h2>

            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">

                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">

                    ETA

                </p>

                <h2
                    id="eta"
                    class="mt-3 text-3xl font-black text-white">

                    --

                </h2>

            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">

                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">

                    Carbon

                </p>

                <h2
                    id="carbon"
                    class="mt-3 text-3xl font-black text-emerald-400">

                    --

                </h2>

            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">

                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">

                    Status

                </p>

                <h2
                    id="shipmentStatus"
                    class="mt-3 text-2xl font-black text-cyan-300">

                </h2>

            </div>

        </div>

        {{-- AI Status --}}
        <div class="border-t border-white/10 px-10 py-8">

            <div
                class="flex items-center justify-between rounded-3xl border border-cyan-500/20 bg-cyan-500/5 px-8 py-6">

                <div>

                    <p class="uppercase tracking-[0.35em] text-xs font-black text-cyan-400">

                        AI System

                    </p>

                    <h2 class="mt-2 text-2xl font-black text-white">

                        Ready For Simulation

                    </h2>

                    <p class="mt-2 text-slate-400">

                        Configure the parameters below to launch the Digital Twin.

                    </p>

                </div>

                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-cyan-500/10 text-3xl">

                    🤖

                </div>

            </div>

        </div>

        {{-- Shipment Timeline --}}
<div class="border-t border-white/10 px-10 py-8">

    <div class="flex items-center justify-between">

        <div>

            <p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">

                Shipment Timeline

            </p>

            <h2 class="mt-2 text-2xl font-black text-white">

                Logistics Progress

            </h2>

        </div>

<div
    id="timelineBadge"
    class="rounded-full bg-cyan-500/10 px-4 py-2 text-cyan-300 font-bold text-sm">

    READY

</div>

    </div>

    <div class="mt-10 flex items-center justify-between relative">

        <div class="absolute left-[10%] right-[10%] top-5 h-[2px] bg-white/10"></div>

        <div id="progressLine"
             class="absolute left-[10%] top-5 h-[2px] bg-gradient-to-r from-cyan-400 to-cyan-300 transition-all duration-700"
             style="width:0%;">
        </div>

        {{-- Harvested --}}
        <div class="timeline-step relative z-10 flex flex-col items-center">

            <div id="stepHarvested"
                 class="h-11 w-11 rounded-full border-2 border-cyan-400 bg-cyan-500 text-white flex items-center justify-center font-bold">

                ✓

            </div>

            <h3 class="mt-4 text-sm font-bold text-slate-300">

                Harvested

            </h3>

        </div>

        {{-- Packed --}}
        <div class="timeline-step relative z-10 flex flex-col items-center">

            <div id="stepPacked"
                 class="h-11 w-11 rounded-full border-2 border-slate-600 bg-slate-800 text-slate-500 flex items-center justify-center font-bold">

                ✓

            </div>

            <h3 class="mt-4 text-sm font-bold text-slate-300">

                Packed

            </h3>

        </div>

        {{-- In Transit --}}
        <div class="timeline-step relative z-10 flex flex-col items-center">

            <div id="stepTransit"
                 class="h-11 w-11 rounded-full border-2 border-slate-600 bg-slate-800 text-slate-500 flex items-center justify-center font-bold">

                🚚

            </div>

            <h3 class="mt-4 text-sm font-bold text-slate-300">

                In Transit

            </h3>

        </div>

        {{-- Delivered --}}
        <div class="timeline-step relative z-10 flex flex-col items-center">

            <div id="stepDelivered"
                 class="h-11 w-11 rounded-full border-2 border-slate-600 bg-slate-800 text-slate-500 flex items-center justify-center font-bold">

                📦

            </div>

            <h3 class="mt-4 text-sm font-bold text-slate-300">

                Delivered

            </h3>

        </div>

    </div>

</div>

    </div>

</div>

</div>
<div id="simulationPanel" class="hidden mt-10 space-y-8">

    <div class="relative overflow-hidden rounded-[36px]
bg-gradient-to-br
from-slate-950
via-slate-900
to-slate-900
border border-cyan-500/20
shadow-[0_0_70px_rgba(34,211,238,.10)]
p-10">

{{-- Background Glow --}}
<div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>

<div class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl"></div>

{{-- Grid Pattern --}}
<div
class="absolute inset-0 opacity-[0.04]"
style="
background-image:
linear-gradient(to right,#ffffff 1px,transparent 1px),
linear-gradient(to bottom,#ffffff 1px,transparent 1px);
background-size:32px 32px;">
</div>

<div class="relative z-10">
<div class="mt-8 overflow-hidden rounded-[32px]
border border-cyan-500/20
bg-gradient-to-br
from-slate-950
via-slate-900
to-cyan-950
shadow-[0_0_50px_rgba(34,211,238,.12)]">

<div class="px-4 py-6 sm:px-6 md:px-8 md:py-8">

    <p class="uppercase tracking-[0.3em] md:tracking-[0.45em] text-cyan-400 text-[10px] md:text-xs font-black">
        AI Control Center
    </p>

    <h2 class="mt-2 text-2xl md:text-3xl font-black text-white">
        Configure Digital Twin
    </h2>

    <p class="mt-2 max-w-2xl text-sm md:text-base text-slate-400 leading-6 md:leading-7">
        Modify operational variables before launching the simulation.
    </p>

    <div class="mt-8 md:mt-12">

        <p class="uppercase tracking-[0.25em] md:tracking-[0.3em] text-slate-400 text-[10px] md:text-xs font-bold">
            Vehicle Type
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mt-5 md:mt-6">

            <!-- Truck -->
            <div class="vehicle-card group cursor-pointer rounded-2xl md:rounded-3xl border border-slate-700 bg-slate-900 hover:border-cyan-400 transition-all duration-500 p-5 md:p-8 text-center"
                data-value="Truck">

                <div class="vehicle-icon text-3xl md:text-4xl transition-all duration-500">
                    🚚
                </div>

                <h2 class="vehicle-title mt-4 md:mt-5 text-slate-300 font-black text-lg transition-colors duration-500">
                    Truck
                </h2>

                <p class="vehicle-desc text-sm md:text-base text-slate-500 mt-2 transition-colors duration-500">
                    Standard logistics
                </p>

            </div>

            <!-- Cold Truck -->
            <div class="vehicle-card group cursor-pointer rounded-2xl md:rounded-3xl border border-slate-700 bg-slate-900 hover:border-cyan-400 transition-all duration-500 p-5 md:p-8 text-center"
                data-value="cold">

                <div class="vehicle-icon text-3xl md:text-4xl transition-all duration-500">
                    ❄
                </div>

                <h2 class="vehicle-title mt-4 md:mt-5 text-slate-300 font-black text-lg transition-colors duration-500">
                    Cold Truck
                </h2>

                <p class="vehicle-desc text-sm md:text-base text-slate-500 mt-2 transition-colors duration-500">
                    Temperature controlled
                </p>

            </div>

            <!-- Air Cargo -->
            <div class="vehicle-card group cursor-pointer rounded-2xl md:rounded-3xl border border-slate-700 bg-slate-900 hover:border-cyan-400 transition-all duration-500 p-5 md:p-8 text-center"
                data-value="plane">

                <div class="vehicle-icon text-3xl md:text-4xl transition-all duration-500">
                    ✈
                </div>

                <h2 class="vehicle-title mt-4 md:mt-5 text-slate-300 font-black text-lg transition-colors duration-500">
                    Air Cargo
                </h2>

                <p class="vehicle-desc text-sm md:text-base text-slate-500 mt-2 transition-colors duration-500">
                    Fast delivery
                </p>

            </div>

            <!-- Sea Cargo -->
            <div class="vehicle-card group cursor-pointer rounded-2xl md:rounded-3xl border border-slate-700 bg-slate-900 hover:border-cyan-400 transition-all duration-500 p-5 md:p-8 text-center"
                data-value="ship">

                <div class="vehicle-icon text-3xl md:text-4xl transition-all duration-500">
                    🚢
                </div>

                <h2 class="vehicle-title mt-4 md:mt-5 text-slate-300 font-black text-lg transition-colors duration-500">
                    Sea Cargo
                </h2>

                <p class="vehicle-desc text-sm md:text-base text-slate-500 mt-2 transition-colors duration-500">
                    Low carbon
                </p>

            </div>

        </div>

    </div>

</div>

<input type="hidden" id="vehicle" value="Truck">

</div>

</div>


</div>

{{-- Operational Parameters --}}
<div class="mt-10 grid lg:grid-cols-2 gap-6">

    {{-- Temperature --}}
    <div
class="relative overflow-hidden
rounded-3xl
border border-cyan-500/20
bg-gradient-to-br
from-slate-950
via-slate-900
to-cyan-950
p-7
shadow-[0_0_35px_rgba(34,211,238,.10)]
hover:border-cyan-400/40
hover:shadow-[0_0_45px_rgba(34,211,238,.18)]
transition-all duration-500">

        <div class="flex items-center justify-between">

            <div>

                <p class="uppercase tracking-[0.3em] text-xs text-slate-500 font-bold">
                    Storage Temperature
                </p>

                <h2 class="mt-2 text-2xl font-black text-white">
                    Temperature Control
                </h2>

            </div>

            <div
                id="tempValue"
                class="rounded-2xl bg-cyan-500/10 px-5 py-3 text-2xl font-black text-cyan-300">

                5°C

            </div>

        </div>

        <input
            id="temperature"
            type="range"
            min="0"
            max="25"
            value="5"
            class="mt-8 w-full accent-cyan-500">

        <div class="mt-4 flex justify-between text-xs uppercase tracking-wider text-slate-500">

            <span>0°C</span>

            <span class="text-emerald-400 font-bold">
                Optimal
            </span>

            <span>25°C</span>

        </div>

    </div>

    {{-- Delay --}}
    <div
class="relative overflow-hidden
rounded-3xl
border border-cyan-500/20
bg-gradient-to-br
from-slate-950
via-slate-900
to-cyan-950
p-7
shadow-[0_0_35px_rgba(34,211,238,.10)]
hover:border-cyan-400/40
hover:shadow-[0_0_45px_rgba(34,211,238,.18)]
transition-all duration-500">

        <div class="flex items-center justify-between">

            <div>

                <p class="uppercase tracking-[0.3em] text-xs text-slate-500 font-bold">
                    Delivery Delay
                </p>

                <h2 class="mt-2 text-2xl font-black text-white">
                    Transit Delay
                </h2>

            </div>

            <div
                id="delayValue"
                class="rounded-2xl bg-amber-500/10 px-5 py-3 text-2xl font-black text-amber-300">

                0 Day

            </div>

        </div>

        <input
            id="delay"
            type="range"
            min="0"
            max="10"
            value="0"
            class="mt-8 w-full accent-amber-500">

        <div class="mt-4 flex justify-between text-xs uppercase tracking-wider text-slate-500">

            <span>0</span>

            <span>5</span>

            <span>10 Days</span>

        </div>

    </div>

</div>

<div
    class="relative overflow-hidden
    mt-6
    rounded-2xl md:rounded-3xl
    border border-cyan-500/20
    bg-gradient-to-br
    from-slate-950
    via-slate-900
    to-cyan-950
    shadow-[0_0_35px_rgba(34,211,238,.12)]
    p-5 md:p-6">

    <!-- Glow -->
    <div class="absolute inset-0 opacity-20">

        <div class="absolute -top-20 -left-16 w-56 h-56 rounded-full bg-cyan-500 blur-3xl"></div>

        <div class="absolute bottom-0 right-0 w-52 h-52 rounded-full bg-indigo-500 blur-3xl"></div>

    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">

        <div>

            <p class="uppercase tracking-[0.2em] md:tracking-[0.3em] text-[10px] md:text-xs text-cyan-400 font-black">
                AI Navigation
            </p>

            <h2 class="mt-2 text-xl md:text-2xl font-black text-white">
                Smart Route Optimization
            </h2>

            <p class="mt-2 text-sm md:text-base text-slate-400 leading-6 md:leading-7">
                AI automatically searches the fastest and most sustainable logistics route.
            </p>

        </div>

        <label class="relative inline-flex cursor-pointer self-start md:self-auto">

            <input
                id="route"
                type="checkbox"
                checked
                class="peer sr-only">

            <div class="h-10 w-18 md:h-11 md:w-20 rounded-full bg-slate-700 transition peer-checked:bg-cyan-500"></div>

            <div class="absolute left-1 top-1 h-8 w-8 md:h-9 md:w-9 rounded-full bg-white transition-all peer-checked:translate-x-8 md:peer-checked:translate-x-9"></div>

        </label>

    </div>

</div>

<button
    id="runSimulation"
    data-url="{{ route('simulation.run') }}"
    class="group relative mt-8 w-full overflow-hidden
    rounded-2xl md:rounded-[28px]
    border border-cyan-400/30
    bg-gradient-to-r
    from-cyan-500
    via-sky-500
    to-indigo-600
    px-5 py-4 md:px-7 md:py-5
    transition-all duration-500
    hover:-translate-y-1
    hover:shadow-[0_0_60px_rgba(34,211,238,.40)]
    active:scale-[.98]">

    <!-- Shine -->
    <div
        class="absolute inset-0
        bg-gradient-to-r
        from-white/20
        via-transparent
        to-transparent
        translate-x-[-100%]
        group-hover:translate-x-[100%]
        transition-transform duration-1000">
    </div>

    <div class="relative flex items-center justify-between gap-4">

        <!-- Left -->
        <div class="flex items-center gap-3 md:gap-5">

            <div
                class="flex h-11 w-11 md:h-14 md:w-14
                items-center justify-center
                rounded-xl md:rounded-2xl
                bg-white/20
                backdrop-blur-md
                text-2xl md:text-3xl">

                ⚡

            </div>

            <div class="text-left">

                <p
                    class="uppercase
                    tracking-[0.2em] md:tracking-[0.35em]
                    text-[10px] md:text-xs
                    font-bold
                    text-cyan-100">

                    AI Simulation

                </p>

                <h2
                    class="mt-1
                    text-lg md:text-2xl
                    font-black
                    text-white">

                    Launch Digital Twin

                </h2>

            </div>

        </div>

        <!-- Arrow -->
        <div
            class="flex h-10 w-10 md:h-12 md:w-12
            items-center justify-center
            rounded-full
            bg-white/15
            backdrop-blur-md
            transition-transform duration-500
            group-hover:translate-x-1 md:group-hover:translate-x-2">

            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 md:h-6 md:w-6 text-white"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7" />

            </svg>

        </div>

    </div>

</button>

<div id="result"></div>
</div>
</div>

</div>

</div>

</div>
<div
    id="simulationLoading"
    class="fixed inset-0 bg-slate-950/95 backdrop-blur-xl z-[99999] hidden items-center justify-center">

    <div class="w-full max-w-xl">
        <div class="text-center">
            <div class="mx-auto w-28 h-28 rounded-full bg-gradient-to-r from-cyan-500 to-indigo-600 flex items-center justify-center text-5xl shadow-2xl shadow-cyan-500/40 animate-pulse">
                🤖
            </div>

            <h1 class="mt-8 text-5xl font-black text-white">
                AI DIGITAL TWIN
            </h1>

            <p
                id="loadingText"
                class="mt-4 text-cyan-300 text-lg">
                Synchronizing Shipment...
            </p>
        </div>

        <div class="mt-12">
            <div class="w-full h-4 rounded-full bg-slate-700 overflow-hidden">
                <div
                    id="loadingBar"
                    class="h-full bg-gradient-to-r from-cyan-400 via-indigo-500 to-emerald-400"
                    style="width:0%">
                </div>
            </div>

            <p
                id="loadingPercent"
                class="mt-4 text-center text-white font-black text-3xl">
                0%
            </p>
        </div>

        <div class="mt-12 space-y-4">
            <div id="step1" class="text-slate-500">
                ○ Loading shipment telemetry
            </div>

            <div id="step2" class="text-slate-500">
                ○ Building Digital Twin
            </div>

            <div id="step3" class="text-slate-500">
                ○ Optimizing logistics
            </div>

            <div id="step4" class="text-slate-500">
                ○ Calculating carbon emission
            </div>

            <div id="step5" class="text-slate-500">
                ○ Predicting operational risk
            </div>

            <div id="step6" class="text-slate-500">
                ○ Generating recommendation
            </div>
        </div>
    </div>
</div>
<script>
    
document.querySelectorAll(".vehicle-card").forEach(card=>{

card.onclick=function(){

document.querySelectorAll(".vehicle-card").forEach(c=>{

c.classList.remove(
"bg-white",
"border-cyan-500",
"shadow-2xl",
"scale-105"
);

c.classList.add(
"bg-slate-900",
"border-slate-700"
);

c.querySelector(".vehicle-title")
.classList.remove("text-black");

c.querySelector(".vehicle-title")
.classList.add("text-slate-300");

c.querySelector(".vehicle-desc")
.classList.remove("text-slate-700");

c.querySelector(".vehicle-desc")
.classList.add("text-slate-500");

c.querySelector(".vehicle-icon")
.classList.remove("scale-125");

});

this.classList.remove(
"bg-slate-900",
"border-slate-700"
);

this.classList.add(
"bg-white",
"border-cyan-500",
"shadow-2xl",
"scale-105"
);

this.querySelector(".vehicle-title")
.classList.remove("text-slate-300");

this.querySelector(".vehicle-title")
.classList.add("text-black");

this.querySelector(".vehicle-desc")
.classList.remove("text-slate-500");

this.querySelector(".vehicle-desc")
.classList.add("text-slate-700");

this.querySelector(".vehicle-icon")
.classList.add("scale-125");

document.getElementById("vehiclePreview").textContent =
this.querySelector(".vehicle-title").textContent;

};

});

temperature.oninput = () => {
    tempValue.innerHTML = temperature.value + "°C";
};

delay.oninput = () => {
    delayValue.innerHTML = delay.value + " Day";
};

const preview = document.getElementById("shipmentPreview");

function showSimulationResult(data){

const improvement =
    data.before.risk_score -
    data.after.risk_score;

const html=`

<div class="mt-10 md:mt-14 rounded-2xl md:rounded-[34px] overflow-hidden border border-slate-200 shadow-2xl">

    <div
        class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950 px-5 py-8 sm:px-8 md:px-12 md:py-12">

        <!-- Glow -->
        <div
            class="absolute -top-20 -right-20 md:-top-24 md:-right-24 h-56 w-56 md:h-80 md:w-80 rounded-full bg-cyan-500/10 blur-3xl">
        </div>

        <div
            class="absolute -bottom-16 left-0 md:-bottom-20 h-44 w-44 md:h-64 md:w-64 rounded-full bg-indigo-500/10 blur-3xl">
        </div>

        <div class="relative z-10">

            <!-- Header -->
            <div class="flex flex-col xl:flex-row xl:justify-between gap-8">

                <!-- Left -->
                <div class="flex-1">

                    <div
                        class="inline-flex items-center gap-3 rounded-full border border-cyan-400/20 bg-cyan-500/10 px-4 py-2 md:px-5">

                        <span class="h-2.5 w-2.5 md:h-3 md:w-3 rounded-full bg-emerald-400 animate-pulse"></span>

                        <span
                            class="text-[10px] md:text-xs font-black tracking-[0.2em] md:tracking-[0.3em] uppercase text-cyan-300">
                            AI DIGITAL TWIN
                        </span>

                    </div>

                    <h1
                        class="mt-5 md:mt-6 text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black text-white leading-tight">
                        Simulation Result
                    </h1>

                    <p
                        class="mt-4 md:mt-5 max-w-3xl text-sm md:text-base lg:text-lg leading-7 md:leading-8 text-slate-300">
                        AI has analyzed the logistics scenario and generated the
                        optimal operational strategy based on sustainability,
                        transportation efficiency, and delivery risk.
                    </p>

                </div>

                <!-- Right -->
                <div class="grid grid-cols-2 xl:grid-cols-1 gap-4 w-full xl:w-72">

                    <div
                        class="rounded-2xl md:rounded-3xl border border-emerald-400/20 bg-emerald-500/10 px-5 py-4">

                        <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-emerald-300">
                            Status
                        </p>

                        <h2 class="mt-2 text-xl md:text-2xl font-black text-white">
                            Completed
                        </h2>

                    </div>

                    <div
                        class="rounded-2xl md:rounded-3xl border border-cyan-400/20 bg-cyan-500/10 px-5 py-4">

                        <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-cyan-300">
                            AI Confidence
                        </p>

                        <h2 class="mt-2 text-2xl md:text-3xl font-black text-white">
                            98.7%
                        </h2>

                    </div>

                </div>

            </div>

            <!-- KPI -->
            <div class="mt-8 md:mt-12 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6">

                <div class="rounded-2xl md:rounded-3xl border border-white/10 bg-white/5 p-5 md:p-6">

                    <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-slate-500">
                        Risk Reduced
                    </p>

                    <h2 class="mt-3 text-3xl md:text-4xl font-black text-emerald-400">
                        ${improvement}%
                    </h2>

                </div>

                <div class="rounded-2xl md:rounded-3xl border border-white/10 bg-white/5 p-5 md:p-6">

                    <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-slate-500">
                        AI Model
                    </p>

                    <h2 class="mt-3 text-xl md:text-2xl font-black text-white">
                        Digital Twin
                    </h2>

                </div>

                <div class="rounded-2xl md:rounded-3xl border border-white/10 bg-white/5 p-5 md:p-6">

                    <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-slate-500">
                        Sustainability
                    </p>

                    <h2 class="mt-3 text-3xl md:text-4xl font-black text-cyan-300">
                        ${data.after.sustainability_score}
                    </h2>

                </div>

                <div class="rounded-2xl md:rounded-3xl border border-white/10 bg-white/5 p-5 md:p-6">

                    <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-slate-500">
                        Carbon Difference
                    </p>

                    <h2 class="mt-3 text-3xl md:text-4xl font-black text-emerald-400">
                        ${data.after.carbon_saved.toFixed(1)} kg
                    </h2>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="bg-white p-10">

<div class="grid xl:grid-cols-3 gap-8 mt-12">

    <!-- BEFORE -->
    <div
    class="rounded-[30px]
    border border-rose-500/20
    bg-gradient-to-br
    from-slate-950
    to-slate-900
    p-8">

        <div class="flex justify-between items-center">

            <div>

                <p class="uppercase tracking-[0.35em]
                text-rose-300 text-xs font-black">

                    Current Scenario

                </p>

                <h2 class="mt-3 text-3xl font-black text-white">

                    Before AI

                </h2>

            </div>

            <div
            class="h-14 w-14 rounded-2xl
            bg-rose-500/20
            flex items-center justify-center
            text-2xl">

                ⚠️

            </div>

        </div>

        <div class="mt-10 space-y-8">

            <div>

                <p class="text-slate-400">

                    Operational Risk

                </p>

                <h1
                class="counter text-6xl font-black text-rose-400"
                data-value="${data.before.risk_score}">

                    0

                </h1>

            </div>

            <div class="space-y-5">

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Carbon

                    </span>

                    <span
                    class="counter font-black text-white"
                    data-value="${data.before.carbon}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        ETA

                    </span>

                    <span
                    class="counter font-black text-white"
                    data-value="${data.before.duration}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Sustainability

                    </span>

                    <span
                    class="counter font-black text-white"
                    data-value="${data.before.sustainability_score}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Vehicle

                    </span>

                    <span class="font-black text-white">

                        ${data.before.vehicle}

                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- AI CENTER -->
    <div
    class="flex flex-col items-center justify-center">

        <div
        class="relative h-36 w-36 rounded-full
        bg-gradient-to-br
        from-cyan-500
        to-indigo-600
        shadow-[0_0_70px_rgba(34,211,238,.35)]
        flex items-center justify-center">

            <div
            class="absolute inset-2 rounded-full
            border border-white/20"></div>

            <span class="text-6xl">

                🧠

            </span>

        </div>

        <h2 class="mt-8 text-3xl font-black">

            AI Optimization

        </h2>

        <p class="mt-3 text-center text-slate-400 leading-7">

            Digital Twin analyzed logistics,
            transportation efficiency,
            sustainability and operational risk.

        </p>

        <div class="mt-8 space-y-3">

            <div class="rounded-full bg-emerald-500/10 px-5 py-2 text-emerald-400 font-bold">

                Carbon Difference

            </div>

            <div class="rounded-full bg-cyan-500/10 px-5 py-2 text-cyan-400 font-bold">

                Sustainability Difference

            </div>

            <div class="rounded-full bg-indigo-500/10 px-5 py-2 text-indigo-300 font-bold">

                Delivery Difference

            </div>

        </div>

    </div>

    <!-- AFTER -->
    <div
    class="rounded-[30px]
    border border-cyan-500/20
    bg-gradient-to-br
    from-cyan-950
    via-slate-900
    to-slate-950
    p-8">

        <div class="flex justify-between items-center">

            <div>

                <p class="uppercase tracking-[0.35em]
                text-cyan-300 text-xs font-black">

                    AI Optimized

                </p>

                <h2 class="mt-3 text-3xl font-black text-white">

                    After AI

                </h2>

            </div>

            <div
            class="h-14 w-14 rounded-2xl
            bg-cyan-500/20
            flex items-center justify-center
            text-2xl">

                🚀

            </div>

        </div>

        <div class="mt-10 space-y-8">

            <div>

                <p class="text-slate-400">

                    Operational Risk

                </p>

                <h1
                class="counter text-6xl font-black text-cyan-300"
                data-value="${data.after.risk_score}">

                    0

                </h1>

            </div>

            <div class="space-y-5">

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Carbon

                    </span>

                    <span
                    class="counter font-black text-white"
                    data-value="${data.after.carbon}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        ETA

                    </span>

                    <span
                    class="counter font-black text-white"
                    data-value="${data.after.duration}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Sustainability

                    </span>

                    <span
                    class="counter font-black text-emerald-400"
                    data-value="${data.after.sustainability_score}">

                        0

                    </span>

                </div>

                <div class="flex justify-between">

                    <span class="text-slate-400">

                        Vehicle

                    </span>

                    <span class="font-black text-cyan-300">

                        ${data.after.vehicle}

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

<div
    class="mt-10 md:mt-14 rounded-2xl md:rounded-[32px] border border-cyan-500/20 bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950 overflow-hidden shadow-[0_0_60px_rgba(34,211,238,.12)]">

    <!-- HEADER -->
    <div class="px-5 py-6 sm:px-6 md:px-10 md:py-8 border-b border-white/10">

        <p
            class="uppercase tracking-[0.2em] md:tracking-[0.35em] text-cyan-400 text-[10px] md:text-xs font-black">
            Executive AI Summary
        </p>

        <h2 class="mt-3 text-2xl md:text-3xl font-black text-white">
            Optimization Report
        </h2>

        <p class="mt-2 text-sm md:text-base text-slate-400 leading-6">
            AI compared the original shipment against the optimized logistics strategy.
        </p>

    </div>

    <!-- KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 p-5 sm:p-6 md:p-8">

        <div class="rounded-2xl md:rounded-3xl border border-cyan-500/20 bg-cyan-500/10 p-5 md:p-6">

            <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.25em] text-cyan-300">
                Improvement
            </p>

            <h2 class="counter mt-3 text-3xl md:text-4xl lg:text-5xl font-black text-white"
                data-value="${improvement}">
                0
            </h2>

            <p class="mt-2 text-sm md:text-base text-cyan-300 font-semibold">
                %
            </p>

        </div>

        <div class="rounded-2xl md:rounded-3xl border border-emerald-500/20 bg-emerald-500/10 p-5 md:p-6">

            <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.25em] text-emerald-300">
                Carbon Difference
            </p>

            <h2 class="mt-3 text-3xl md:text-4xl lg:text-5xl font-black text-white">
                ${(data.before.carbon-data.after.carbon).toFixed(1)}

            </h2>

            <p class="mt-2 text-sm md:text-base text-emerald-300">
                kg CO₂
            </p>

        </div>

        <div class="rounded-2xl md:rounded-3xl border border-indigo-500/20 bg-indigo-500/10 p-5 md:p-6">

            <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.25em] text-indigo-300">
                ETA Saved
            </p>

            <h2 class="mt-3 text-3xl md:text-4xl lg:text-5xl font-black text-white">
                ${(data.before.duration-data.after.duration).toFixed(1)}
            </h2>

            <p class="mt-2 text-sm md:text-base text-indigo-300">
                hrs
            </p>

        </div>

        <div class="rounded-2xl md:rounded-3xl border border-amber-500/20 bg-amber-500/10 p-5 md:p-6">

            <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] md:tracking-[0.25em] text-amber-300">
                ESG Rating
            </p>

            <h2 class="mt-3 text-3xl md:text-4xl lg:text-5xl font-black text-white">
                A+
            </h2>

            <p class="mt-2 text-sm md:text-base text-amber-300">
                Excellent
            </p>

        </div>

    </div>

    <!-- BODY -->
    <div class="p-5 sm:p-6 md:p-8 pt-0">

        <div class="rounded-2xl md:rounded-3xl border border-white/10 bg-white/5 p-5 md:p-8">

            <p
                class="uppercase tracking-[0.2em] md:tracking-[0.3em] text-cyan-400 text-[10px] md:text-xs font-black">
                AI Recommendation
            </p>

            <div class="mt-5 md:mt-6 space-y-3 md:space-y-4">

                <div
                    class="rounded-xl md:rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 md:px-5 py-3 md:py-4 text-sm md:text-base text-white">
                    ✅ Use <b>${data.after.vehicle}</b>
                </div>

                <div
                    class="rounded-xl md:rounded-2xl bg-cyan-500/10 border border-cyan-500/20 px-4 md:px-5 py-3 md:py-4 text-sm md:text-base text-white">
                    🚚 Smart Route Optimization Enabled
                </div>

                <div
                    class="rounded-xl md:rounded-2xl bg-indigo-500/10 border border-indigo-500/20 px-4 md:px-5 py-3 md:py-4 text-sm md:text-base text-white">
                    🌡 Maintain Temperature
                    <b>${document.getElementById("temperature").value}°C</b>
                </div>

                <div
                    class="rounded-xl md:rounded-2xl bg-amber-500/10 border border-amber-500/20 px-4 md:px-5 py-3 md:py-4 text-sm md:text-base text-white">
                    ⏱ Delay below
                    <b>${document.getElementById("delay").value} day(s)</b>
                </div>

            </div>

        </div>

    </div>

</div>
</div>

`;

document.getElementById("result").innerHTML=html;

animateCounters();

}

function animateCounters(){

document.querySelectorAll(".counter").forEach(el=>{

const target=parseFloat(el.dataset.value);

let value=0;

const speed=target/50;

const timer=setInterval(()=>{

value+=speed;

if(value>=target){

value=target;

clearInterval(timer);

}

el.innerHTML=Math.round(value);

},18);

});

}

document.addEventListener('click', function (e) {
    const card = e.target.closest('.vehicle-card');

    if (!card) return;

    const vehicle = card.dataset.value;

    document.getElementById('vehicle').value = vehicle;

    console.log("Vehicle selected:", vehicle);
});
document.getElementById("runSimulation").addEventListener("click", async function () {
    const btn = this;
    // Gunakan ID yang unik atau pastikan cuma satu
    const overlay = document.getElementById("simulationLoading"); 
    
    overlay.classList.remove("hidden");
    overlay.classList.add("flex");

    const tasks = [
        "Loading Shipment", "Building Digital Twin", "Loading Weather Dataset",
        "Carbon Emission Model", "Shelf-Life Prediction", "Route Optimization",
        "Monte Carlo Simulation", "Neural Decision Engine", "Explainability Analysis",
        "Final Recommendation"
    ];

    let progress = 0;
    const bar = document.getElementById("loadingBar");
    const percent = document.getElementById("loadingPercent");
    const text = document.getElementById("loadingText");

    // Efek progres
    const interval = setInterval(() => {
        if (progress >= 100) clearInterval(interval);
        else {
            progress++;
            bar.style.width = progress + "%";
            percent.innerHTML = progress + "%";
            // Update teks berdasarkan progres
            const taskIndex = Math.floor(progress / 10);
            if(tasks[taskIndex]) text.innerHTML = tasks[taskIndex];
        }
    }, 50);

document.addEventListener('click', function (e) {

    const card = e.target.closest('.vehicle-card');

    if (!card) return;

    const vehicle = card.dataset.value;

    document.getElementById('vehicle').value = vehicle;

    console.log("Vehicle selected:", vehicle);

});

    try {
        const response = await fetch(btn.dataset.url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
body: JSON.stringify({
    shipment: document.getElementById("selectedShipment").value,
    vehicle: document.getElementById("vehicle").value,
    temperature: document.getElementById("temperature").value,
    delay: document.getElementById("delay").value,
    route: document.getElementById("route").checked
})
        });

        const result = await response.json();

        // Selesaikan progress bar sampai 100%
        progress = 100;
        bar.style.width = "100%";
        percent.innerHTML = "100%";

        // Tunggu sedikit supaya transisi terasa natural
        await new Promise(r => setTimeout(r, 500));

        overlay.classList.remove("flex");
        overlay.classList.add("hidden");

        // Panggil fungsi hasil
        showSimulationResult(result);
        
        // Scroll ke hasil
        document.getElementById("result").scrollIntoView({ behavior: 'smooth' });

    } catch (error) {
        console.error("Error:", error);
        alert("Gagal menjalankan simulasi.");
        overlay.classList.add("hidden");
    }
});

const shipmentPreview = document.getElementById("shipmentPreview");
const simulationPanel = document.getElementById("simulationPanel");

function updateTimeline(status){

    // Reset semua step
    document.querySelectorAll(".timeline-step > div").forEach(step=>{
        step.className =
        "h-11 w-11 rounded-full border-2 border-slate-600 bg-slate-800 text-slate-500 flex items-center justify-center font-bold";
    });

    let progress = 0;

    status = status.trim().toLowerCase();

switch(status){

        case "harvested":
            progress = 0;
            break;

        case "packed":
            progress = 1;
            break;

        case "in transit":
            progress = 2;
            break;

        case "delivered":
            progress = 3;
            break;

        default:
            progress = 0;
    }

    const steps = [
        document.getElementById("stepHarvested"),
        document.getElementById("stepPacked"),
        document.getElementById("stepTransit"),
        document.getElementById("stepDelivered")
    ];

    for(let i=0;i<=progress;i++){

        steps[i].classList.remove(
            "bg-slate-800",
            "border-slate-600",
            "text-slate-500"
        );

        steps[i].classList.add(
            "bg-cyan-500",
            "border-cyan-400",
            "text-white"
        );

    }

    document.getElementById("progressLine").style.width =
        ["0%","33%","66%","100%"][progress];

    document.getElementById("timelineBadge").innerHTML = status;

}

document.querySelectorAll(".shipment-card").forEach(card=>{

    card.addEventListener("click",function(){

        document.querySelectorAll(".shipment-card")
            .forEach(c=>c.classList.remove("shipment-active"));

        this.classList.add("shipment-active");

        document.getElementById("selectedShipment").value =
            this.dataset.id;

        shipmentPreview.classList.remove("hidden");
        simulationPanel.classList.remove("hidden");

        document.getElementById("commodity").textContent =
            this.dataset.commodity;

        document.getElementById("origin").textContent =
            this.dataset.origin;

        document.getElementById("destination").textContent =
            this.dataset.destination;

        document.getElementById("distance").textContent =
            this.dataset.distance + " km";

            const distance = parseInt(this.dataset.distance);

document.getElementById("eta").textContent =
    Math.max(1, Math.round(distance / 60)) + " hrs";

document.getElementById("carbon").textContent =
    (distance * 0.08).toFixed(1) + " kg";

        document.getElementById("shipmentStatus").textContent =
            this.dataset.status;
            updateTimeline(card.dataset.status);

    });

});
</script>
<div id="simulationLoading"
class="fixed inset-0 hidden items-center justify-center bg-slate-950/95 backdrop-blur-2xl z-[99999]">

<div class="w-full max-w-2xl rounded-[35px] bg-slate-900 border border-slate-700 p-12 shadow-[0_0_80px_rgba(6,182,212,.15)]">

<div class="flex items-center gap-6">

<div class="w-20 h-20 rounded-3xl bg-gradient-to-r from-cyan-500 to-indigo-600 flex items-center justify-center text-4xl animate-pulse">

🤖

</div>

<div>

<p class="uppercase tracking-[0.35em] text-cyan-400 text-xs font-black">

AI DIGITAL TWIN

</p>

<h1 class="text-4xl font-black text-white mt-2">

Simulation Engine

</h1>

<p id="loadingText"
class="text-slate-400 mt-2">

Synchronizing...

</p>

</div>

</div>

<div class="mt-10">

<div class="h-4 rounded-full bg-slate-800 overflow-hidden">

<div
id="loadingBar"
class="h-full bg-gradient-to-r from-cyan-400 via-indigo-500 to-emerald-400"
style="width:0%">

</div>

</div>

<div class="flex justify-between mt-4">

<p class="text-slate-500">

Simulation Progress

</p>

<p id="loadingPercent"
class="font-black text-white">

0%

</p>

</div>

</div>

<div class="mt-12 space-y-4">

<div id="task0" class="text-slate-500">○ Loading Shipment</div>

<div id="task1" class="text-slate-500">○ Building Digital Twin</div>

<div id="task2" class="text-slate-500">○ Loading Weather Dataset</div>

<div id="task3" class="text-slate-500">○ Carbon Emission Model</div>

<div id="task4" class="text-slate-500">○ Shelf-Life Prediction</div>

<div id="task5" class="text-slate-500">○ Route Optimization</div>

<div id="task6" class="text-slate-500">○ Monte Carlo Simulation</div>

<div id="task7" class="text-slate-500">○ Neural Decision Engine</div>

<div id="task8" class="text-slate-500">○ Explainability Analysis</div>

<div id="task9" class="text-slate-500">○ Final Recommendation</div>

</div>

<div class="mt-10 flex justify-between">

<div>

<p class="text-slate-500 text-sm">

Estimated Remaining Time

</p>

<h2 id="remainingTime"
class="text-white font-black text-2xl">

3.0 s

</h2>

</div>

<div>

<p class="text-slate-500 text-sm">

AI Confidence

</p>

<h2 class="text-emerald-400 font-black text-2xl">

99.8%

</h2>

</div>

</div>

</div>

</div>

<script>
    const preview=document.getElementById("shipmentPreview");

document.querySelectorAll(".shipment-card").forEach(card=>{

    card.onclick=()=>{

        document.querySelectorAll(".shipment-card")
            .forEach(c=>c.classList.remove("shipment-active"));

        card.classList.add("shipment-active");

        preview.classList.remove("hidden");

        preview.animate(

[
{
opacity:0,
transform:"translateY(20px)"
},
{
opacity:1,
transform:"translateY(0)"
}
],

{
duration:350,
easing:"ease-out"
}

);

        document.getElementById("commodity").textContent=
            card.dataset.commodity;

        document.getElementById("origin").textContent=
            card.dataset.origin;

        document.getElementById("destination").textContent=
            card.dataset.destination;

        document.getElementById("distance").textContent=
            card.dataset.distance+" km";

        document.getElementById("shipmentStatus").textContent=
            card.dataset.status;

    }

});

const search=document.getElementById("searchShipment");

search.addEventListener("input",()=>{

const keyword=search.value.toLowerCase();

document.querySelectorAll(".shipment-card")
.forEach(card=>{

const text=card.innerText.toLowerCase();

card.style.display=text.includes(keyword)
?"block"
:"none";

});

});
</script>
</x-app-layout>