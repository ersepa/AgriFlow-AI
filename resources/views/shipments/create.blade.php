<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-12 px-6">
        <div class="max-w-2xl mx-auto">
            
            <div class="mb-8 border-b border-slate-200 pb-6">
                <h1 class="text-4xl font-black text-slate-900">Add New Shipment</h1>
                <p class="text-slate-500 mt-2">Log a new logistics journey into the AgriFlow AI system.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 relative overflow-hidden">
                
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>

                <form action="{{ route('shipments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Harvest Commodity</label>
                        <select name="harvest_id" class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0">
                            @foreach($harvests as $harvest)
                                <option value="{{ $harvest->id }}">{{ $harvest->commodity }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Origin</label>
                            <input type="text" name="origin" placeholder="e.g. Jakarta" 
                                   class="w-full bg-transparent border-0 focus:ring-0 p-0 font-bold text-slate-800 placeholder-slate-300">
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Destination</label>
                            <input type="text" name="destination" placeholder="e.g. Surabaya" 
                                   class="w-full bg-transparent border-0 focus:ring-0 p-0 font-bold text-slate-800 placeholder-slate-300">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Current Status</label>
                        <select name="status" class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0">
                            <option value="Harvested">Harvested</option>
                            <option value="Packed">Packed</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black hover:bg-indigo-600 transition-all shadow-xl shadow-indigo-200">
                        Save Shipment
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>