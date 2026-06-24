<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-12 px-6">
        <div class="max-w-2xl mx-auto">
            
            <div class="mb-8 border-b border-emerald-100 pb-6">
                <h1 class="text-4xl font-black text-slate-900">Add New Harvest</h1>
                <p class="text-slate-500 mt-2">Log your latest harvest data into the system.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

                <form action="{{ route('harvests.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Commodity Name</label>
                        <input type="text" name="commodity" placeholder="e.g. Arabica Coffee" 
                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0 placeholder-slate-300">
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Weight (KG)</label>
                        <input type="number" name="weight" placeholder="0.00" 
                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0 placeholder-slate-300">
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Location</label>
                        <input type="text" name="location" placeholder="Field/Warehouse location" 
                               class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0 placeholder-slate-300">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Harvest Date</label>
                            <input type="date" name="harvest_date" 
                                   class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0 uppercase">
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Expiry Date</label>
                            <input type="date" name="expiry_date" 
                                   class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 font-bold p-0 uppercase">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-200">
                        Save Harvest Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>