<x-app-layout>
    {{-- Custom Styles & Micro-Interactions --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
        }

        .agri-table-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-table-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
        }

        .btn-animate {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);
        }
    </style>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 animate-card">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-xs font-bold tracking-wide mb-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span>ANALYTICS AUDIT TRAIL</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Analysis History</h1>
                <p class="text-slate-500 mt-1 font-medium text-sm">Tracking past AI predictive risk and sustainability assessments.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('ai-analysis.index') }}" 
                   class="btn-animate group inline-flex items-center gap-2.5 px-6 py-3.5 rounded-2xl bg-white border border-slate-200/90 text-slate-700 font-extrabold text-sm transition-all hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-600 shadow-sm">
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back to Hub</span>
                </a>
            </div>
        </div>

        {{-- MAIN CONTAINER CARD --}}
        <div class="agri-card p-6 sm:p-8">
            
            {{-- BULK ACTIONS BAR --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Selected: <strong id="selected-count" class="text-indigo-600 font-black">0</strong> item(s)
                    </span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    {{-- Form Hapus Selected --}}
                    <form id="bulk-delete-form" action="{{ route('ai-analysis.bulk-destroy') }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids" id="bulk-delete-ids">
                        <button type="button" 
                                id="btn-delete-selected" 
                                onclick="confirmBulkDelete()" 
                                disabled
                                class="btn-animate inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-400 font-extrabold text-xs uppercase tracking-wider transition-all disabled:opacity-50 disabled:cursor-not-allowed border border-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span>Delete Selected</span>
                        </button>
                    </form>

                    {{-- Form Hapus Semua History --}}
                    <form id="delete-all-form" action="{{ route('ai-analysis.truncate') }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                onclick="confirmDeleteAll()" 
                                class="btn-animate inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white font-extrabold text-xs uppercase tracking-wider transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span>Clear All History</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- TABLE WRAPPER --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-slate-500 font-extrabold text-[11px] uppercase tracking-wider">
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" 
                                       id="select-all" 
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-6 py-3">Commodity</th>
                            <th class="px-6 py-3">Risk Level</th>
                            <th class="px-6 py-3">Sustainability Score</th>
                            <th class="px-6 py-3">Waste Prob.</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($analyses as $index => $a)
                            @php
                                $themes = [
                                    0 => ['bg' => 'bg-emerald-50/60 hover:bg-emerald-50', 'border' => 'border-emerald-200/80'],
                                    1 => ['bg' => 'bg-teal-50/60 hover:bg-teal-50', 'border' => 'border-teal-200/80'],
                                    2 => ['bg' => 'bg-indigo-50/60 hover:bg-indigo-50', 'border' => 'border-indigo-200/80'],
                                    3 => ['bg' => 'bg-cyan-50/60 hover:bg-cyan-50', 'border' => 'border-cyan-200/80'],
                                ];
                                $theme = $themes[$index % 4];
                            @endphp
                            
                            <tr class="agri-table-row {{ $theme['bg'] }} border {{ $theme['border'] }} rounded-2xl overflow-hidden">
                                
                                {{-- CHECKBOX ROW --}}
                                <td class="px-4 py-5 text-center rounded-l-2xl border-l border-t border-b {{ $theme['border'] }} bg-white">
                                    <input type="checkbox" 
                                           value="{{ $a->id }}" 
                                           class="row-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                </td>

                                {{-- COMMODITY --}}
                                <td class="px-6 py-5 font-black text-slate-900 text-base border-t border-b {{ $theme['border'] }} bg-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                            🧠
                                        </div>
                                        <span class="truncate">{{ $a->shipment->harvest->commodity ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                
                                {{-- RISK LEVEL BADGE --}}
                                <td class="px-6 py-5 border-t border-b {{ $theme['border'] }} bg-white">
                                    <span class="inline-flex items-center px-3.5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-xl border shadow-sm
                                        @if(str_contains($a->risk_level, 'High')) bg-rose-100 text-rose-800 border-rose-200
                                        @elseif(str_contains($a->risk_level, 'Medium')) bg-amber-100 text-amber-800 border-amber-200
                                        @else bg-emerald-100 text-emerald-800 border-emerald-200 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                            @if(str_contains($a->risk_level, 'High')) bg-rose-500
                                            @elseif(str_contains($a->risk_level, 'Medium')) bg-amber-500
                                            @else bg-emerald-500 @endif"></span>
                                        {{ $a->risk_level }}
                                    </span>
                                </td>

                                {{-- SUSTAINABILITY SCORE --}}
                                <td class="px-6 py-5 border-t border-b {{ $theme['border'] }} bg-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full" style="width: {{ min(100, (float)$a->sustainability_score) }}%"></div>
                                        </div>
                                        <span class="font-mono font-black text-slate-900 text-xs">{{ $a->sustainability_score }}/100</span>
                                    </div>
                                </td>

                                {{-- WASTE PROBABILITY --}}
                                <td class="px-6 py-5 border-t border-b {{ $theme['border'] }} bg-white">
                                    <span class="font-mono font-bold text-slate-600 text-xs bg-slate-100/80 px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-sm">
                                        📉 {{ $a->waste_probability }}
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="px-6 py-5 text-right rounded-r-2xl border-r border-t border-b {{ $theme['border'] }} bg-white">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        {{-- View Details --}}
                                        <a href="{{ route('ai-analysis.show', $a->id) }}" 
                                           class="px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white font-extrabold text-xs transition-all shadow-sm">
                                            View
                                        </a>

                                        {{-- Delete Record Single --}}
                                        <form id="delete-form-{{ $a->id }}" action="{{ route('ai-analysis.destroy', $a->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmDelete('{{ $a->id }}')" 
                                                    class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-400 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center bg-slate-50/50 rounded-2xl border border-slate-200/80">
                                    <div class="max-w-xs mx-auto text-center space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto text-xl shadow-sm">
                                            📜
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">No analysis history found.</p>
                                        <p class="text-slate-400 text-xs">Run your first AI evaluation from the Intelligence Hub.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- SweetAlert2 & Selection Script --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const selectedCountEl = document.getElementById('selected-count');
        const btnDeleteSelected = document.getElementById('btn-delete-selected');
        const bulkDeleteIdsInput = document.getElementById('bulk-delete-ids');

        function updateSelectionState() {
            const selectedIds = Array.from(rowCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            selectedCountEl.textContent = selectedIds.length;
            bulkDeleteIdsInput.value = JSON.stringify(selectedIds);

            if (selectedIds.length > 0) {
                btnDeleteSelected.disabled = false;
                btnDeleteSelected.classList.remove('bg-slate-100', 'text-slate-400', 'border-slate-200');
                btnDeleteSelected.classList.add('bg-rose-500', 'text-white', 'hover:bg-rose-600', 'shadow-md', 'shadow-rose-500/20');
            } else {
                btnDeleteSelected.disabled = true;
                btnDeleteSelected.classList.add('bg-slate-100', 'text-slate-400', 'border-slate-200');
                btnDeleteSelected.classList.remove('bg-rose-500', 'text-white', 'hover:bg-rose-600', 'shadow-md', 'shadow-rose-500/20');
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = rowCheckboxes.length > 0 && selectedIds.length === rowCheckboxes.length;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateSelectionState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });
    });

    // Single Delete
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Data Analisis?',
            text: "Data audit analisis ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl border border-slate-100 shadow-2xl',
                confirmButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider',
                cancelButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    // Bulk Delete Selected
    function confirmBulkDelete() {
        const count = document.getElementById('selected-count').textContent;
        Swal.fire({
            title: `Hapus ${count} Data Terpilih?`,
            text: "Data yang dipilih akan dihapus secara permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, hapus terpilih!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl border border-slate-100 shadow-2xl',
                confirmButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider',
                cancelButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulk-delete-form').submit();
            }
        });
    }

    // Clear All History
    function confirmDeleteAll() {
        Swal.fire({
            title: 'Kosongkan Semua History?',
            text: "Peringatan! Seluruh riwayat audit AI analysis akan dihapus permanen!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, kosongkan semua!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl border border-slate-100 shadow-2xl',
                confirmButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider',
                cancelButton: 'font-extrabold px-6 py-3 rounded-2xl text-xs uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-all-form').submit();
            }
        });
    }
    </script>
</x-app-layout>