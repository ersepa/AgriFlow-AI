<x-app-layout>
    <div class="max-w-5xl mx-auto py-10 px-4">
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-indigo-600 flex items-center gap-2 mb-6 font-bold uppercase text-xs tracking-widest">
            ← BACK TO DASHBOARD
        </a>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h1 class="text-3xl font-black text-slate-900">{{ $shipment->harvest->commodity }}</h1>
                    <p class="text-slate-500 mt-1">Status: <span class="font-bold text-indigo-600">{{ $shipment->status }}</span></p>
                    
                    <div class="grid grid-cols-2 gap-6 mt-8">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Origin</p>
                            <p class="font-bold text-slate-800">{{ $shipment->origin }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Destination</p>
                            <p class="font-bold text-slate-800">{{ $shipment->destination }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 p-8 rounded-3xl text-white">
                    <h2 class="text-sm font-black uppercase tracking-widest mb-6">AI Safety Analysis</h2>
                    @foreach($shipment->aiAnalyses as $analysis)
                    <div class="border-b border-white/10 py-4 last:border-0">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-rose-400">{{ $analysis->risk_level }} Risk</span>
                            <span class="text-xs opacity-60">{{ $analysis->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-300">{{ $analysis->recommendations }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center">
                    <p class="text-[10px] uppercase font-bold text-slate-400">Sustainability Score</p>
                    <p class="text-5xl font-black text-indigo-600 mt-2">
                        {{ $shipment->aiAnalyses->avg('sustainability_score') ?? 0 }}
                    </p>
                </div>

                <div class="bg-emerald-50 p-6 rounded-3xl border border-emerald-100">
                    <h3 class="font-bold text-emerald-900 mb-2">Shipment Data</h3>
                    <p class="text-sm text-emerald-800">Weight: {{ number_format($shipment->harvest->weight, 0) }} KG</p>
                    <p class="text-sm text-emerald-800">Harvest Date: {{ $shipment->harvest->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>