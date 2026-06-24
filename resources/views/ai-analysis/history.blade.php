<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- HEADER -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Analysis History</h1>
                <p class="text-slate-500 mt-2 font-medium">Tracking past AI sustainability assessments.</p>
            </div>
<a href="{{ route('ai-analysis.index') }}" 
   class="group inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold transition-all duration-300 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 shadow-sm hover:shadow-indigo-100">
    
    <svg class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    
    <span>Back to Hub</span>
</a>
        </div>

        <!-- HISTORY TABLE -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Commodity</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Risk Level</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Sustainability Score</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waste Prob.</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($analyses as $a)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6 font-bold text-slate-900">{{ $a->shipment->harvest->commodity }}</td>
                        
                        <!-- RISK LEVEL BADGE -->
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full border
                                @if($a->risk_level == 'High') bg-red-50 text-red-600 border-red-100
                                @elseif($a->risk_level == 'Medium') bg-amber-50 text-amber-600 border-amber-100
                                @else bg-emerald-50 text-emerald-600 border-emerald-100 @endif">
                                {{ $a->risk_level }}
                            </span>
                        </td>

                        <!-- SCORE WITH PROGRESS STYLE -->
                        <td class="px-8 py-6 font-mono font-bold text-slate-700">
                            {{ $a->sustainability_score }}/100
                        </td>

                        <!-- WASTE PROBABILITY -->
                        <td class="px-8 py-6 font-mono text-slate-500">
                            {{ $a->waste_probability }}
                        </td>
<td class="px-8 py-6 text-right flex items-center justify-end gap-3">
    <a href="{{ route('ai-analysis.show', $a->id) }}" 
       class="text-indigo-500 hover:text-indigo-700 font-bold text-xs transition-colors">
       View
    </a>

    <form id="delete-form-{{ $a->id }}" action="{{ route('ai-analysis.destroy', $a->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="button" onclick="confirmDelete('{{ $a->id }}')" class="text-rose-500 hover:text-rose-700 font-bold text-xs transition-colors">
            Delete
        </button>
    </form>
</td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-16 text-center text-slate-400 italic">No history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // warna merah rose-600
        cancelButtonColor: '#94a3b8', // warna abu-abu
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'font-bold px-6 py-2',
            cancelButton: 'font-bold px-6 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Ini bakal nge-submit form yang ID-nya sesuai
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>
</x-app-layout>