<x-app-layout>
<style>
    @keyframes fadeIn { 
        0% { opacity: 0; transform: translateY(20px); } 
        100% { opacity: 1; transform: translateY(0); } 
    }
    .row-animate { animation: fadeIn 0.6s ease-out forwards; opacity: 0; }
</style>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Shipment Management</h1>
                <p class="text-slate-500 mt-1">Track and manage your agricultural logistics flow.</p>
            </div>
            <a href="{{ route('shipments.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-200">
                + Add Shipment
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">

                @if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-50 text-emerald-600 font-bold border border-emerald-100">
        {{ session('success') }}
    </div>
@endif

<div class="overflow-x-auto">
<table class="w-full text-left border-separate border-spacing-y-4">
    <thead>
        <tr class="bg-slate-900 text-white uppercase text-[10px] font-black tracking-widest">
            <th class="w-[20%] px-8 py-5 rounded-l-2xl">Commodity</th>
            <th class="w-[20%] px-8 py-5">Origin</th>
            <th class="w-[20%] px-8 py-5">Destination</th>
            <th class="w-[20%] px-8 py-5">Status</th>
<th class="px-8 py-5 rounded-r-2xl text-right pr-12">Action</th>
        </tr>
    </thead>
<tbody>
    @forelse($shipments as $index => $shipment)
    <tr class="row-animate bg-white border border-slate-100 shadow-sm hover:shadow-lg transition-all rounded-3xl">
        
        <td class="px-8 py-6 font-black text-slate-800 text-lg rounded-l-2xl">
            {{ $shipment->harvest->commodity }}
        </td>
        
        <td class="px-8 py-6 font-bold text-slate-600">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                {{ $shipment->origin }}
            </div>
        </td>

        <td class="px-8 py-6 font-bold text-slate-600">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                {{ $shipment->destination }}
            </div>
        </td>
        
        <td class="px-8 py-6">
            <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border
                @if($shipment->status == 'Delivered') bg-emerald-50 text-emerald-600 border-emerald-100
                @elseif($shipment->status == 'In Transit') bg-blue-50 text-blue-600 border-blue-100
                @elseif($shipment->status == 'Packed') bg-purple-50 text-purple-600 border-purple-100
                @else bg-amber-50 text-amber-600 border-amber-100 @endif">
                {{ $shipment->status }}
            </span>
        </td>

        <td class="px-8 py-6 text-right rounded-r-2xl">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="openEditModal('{{ route('shipments.update', $shipment->id) }}')" 
                        class="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all border border-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
                <form action="{{ route('shipments.destroy', $shipment->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all border border-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="py-20 text-center text-slate-400 font-bold">No shipment data available.</td>
    </tr>
    @endforelse
</tbody>
</table>
</div>
        </div>
    </div>
    <div id="editModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:2rem; max-width:400px; width:90%;">
        <h2 style="font-weight:900; font-size:1.5rem;">Update Status</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PATCH')
            <select name="status" class="w-full mt-4 p-3 rounded-xl border border-slate-200">
                <option value="Harvested">Harvested</option>
                <option value="Packed">Packed</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
            </select>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeEditModal()" class="w-full bg-slate-100 py-3 rounded-xl font-bold">Batal</button>
                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(url, currentStatus) {
        document.getElementById('editForm').action = url;
        document.getElementById('editModal').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>
</x-app-layout>