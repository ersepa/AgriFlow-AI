<x-app-layout>
<style>
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .table-row { animation: slideDown 0.5s ease-out forwards; }
    /* Efek glassmorphism halus pada baris */
    .row-hover:hover { background: rgba(248, 250, 252, 0.8); transform: scale(1.002); transition: all 0.2s ease; }
</style>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Harvest Records</h1>
                <p class="text-slate-500 mt-2 font-medium">Log and manage your agricultural output.</p>
            </div>
            <a href="{{ route('harvests.create') }}" 
               class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold transition-all hover:bg-emerald-700 hover:scale-[1.02] shadow-lg shadow-emerald-200">
                <span>Add New Harvest</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            @if(session('success'))
                <div class="m-6 p-4 bg-emerald-50 text-emerald-700 font-bold rounded-2xl border border-emerald-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

<div class="overflow-x-auto">
    <table class="w-full text-left border-separate border-spacing-y-4">
        <thead>
            <tr class="bg-slate-900 text-white">
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest rounded-l-2xl border-l border-t border-b border-slate-800">Commodity</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest border-t border-b border-slate-800">Weight</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest border-t border-b border-slate-800">Location</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest border-t border-b border-slate-800">Date</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest rounded-r-2xl text-right border-r border-t border-b border-slate-800">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($harvests as $index => $harvest)
            <tr class="bg-slate-50 border border-slate-200 shadow-sm rounded-2xl overflow-hidden hover:bg-emerald-50/50 transition-colors duration-300">
                <td class="px-8 py-6 font-black text-slate-800 text-lg border-l border-slate-200 rounded-l-2xl">
                    {{ $harvest->commodity }}
                </td>
                <td class="px-8 py-6">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-[11px] font-black tracking-wider shadow-md shadow-indigo-200">
                        {{ number_format($harvest->weight, 0) }} KG
                    </span>
                </td>
                <td class="px-8 py-6 text-slate-600 font-bold flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $harvest->location }}
                </td>
                <td class="px-8 py-6 text-slate-500 font-mono text-sm font-bold">
                    {{ \Carbon\Carbon::parse($harvest->harvest_date)->format('M d, Y') }}
                </td>
                <td class="px-8 py-6 text-right border-r border-slate-200 rounded-r-2xl">
                    <form action="{{ route('harvests.destroy', $harvest->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button class="p-3 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 shadow-sm transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-16 text-center text-slate-400">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
        </div>
    </div>
</x-app-layout>