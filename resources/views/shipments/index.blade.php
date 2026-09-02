<x-app-layout>
    {{-- Custom Styles & Micro-Interactions --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-table-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-table-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
        }
    </style>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 animate-card">
        
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-100/80 border border-teal-200 text-teal-800 text-xs font-bold tracking-wide mb-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>ACTIVE LOGISTICS OPERATIONS</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Active Shipments</h1>
                <p class="text-slate-500 mt-1 font-medium text-sm">Manage shipments that are still inside the active operational decision cycle.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('completed-shipments.index') }}"
                   class="inline-flex items-center justify-center gap-2 border border-slate-200 bg-white text-slate-700 px-5 py-3.5 rounded-2xl font-extrabold text-sm hover:bg-slate-50 transition-colors">
                    <span>Completed Shipments</span>
                </a>
                <a href="{{ route('shipments.create') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 via-indigo-600 to-indigo-700 text-white px-6 py-3.5 rounded-2xl font-extrabold text-sm transition-all hover:from-teal-700 hover:to-indigo-800 hover:shadow-lg hover:shadow-indigo-600/20 hover:-translate-y-0.5">
                    <span>+ Add Shipment</span>
                </a>
            </div>
        </div>

        {{-- Main Container Card --}}
        <div class="agri-card p-6 sm:p-8">
            
            {{-- Flash Session Success Notification --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 font-bold text-sm rounded-2xl border border-emerald-200/80 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    </div>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Table Wrapper --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-slate-500 font-extrabold text-[11px] uppercase tracking-wider">
                            <th class="w-[22%] px-6 py-3">Commodity</th>
                            <th class="w-[22%] px-6 py-3">Origin</th>
                            <th class="w-[22%] px-6 py-3">Destination</th>
                            <th class="w-[18%] px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $index => $shipment)
                            @php
                                // Variasi warna baris bertema AgriFlow AI Logistics (Teal, Indigo, Emerald, Cyan)
                                $styles = [
                                    0 => [
                                        'bg' => 'bg-teal-50/60 hover:bg-teal-50',
                                        'border' => 'border-teal-200/80',
                                        'icon_bg' => 'bg-teal-100 border-teal-200 text-teal-700',
                                    ],
                                    1 => [
                                        'bg' => 'bg-indigo-50/60 hover:bg-indigo-50',
                                        'border' => 'border-indigo-200/80',
                                        'icon_bg' => 'bg-indigo-100 border-indigo-200 text-indigo-700',
                                    ],
                                    2 => [
                                        'bg' => 'bg-emerald-50/60 hover:bg-emerald-50',
                                        'border' => 'border-emerald-200/80',
                                        'icon_bg' => 'bg-emerald-100 border-emerald-200 text-emerald-700',
                                    ],
                                    3 => [
                                        'bg' => 'bg-cyan-50/60 hover:bg-cyan-50',
                                        'border' => 'border-cyan-200/80',
                                        'icon_bg' => 'bg-cyan-100 border-cyan-200 text-cyan-700',
                                    ]
                                ];

                                $theme = $styles[$index % 4];
                            @endphp

                        <tr class="agri-table-row {{ $theme['bg'] }} border {{ $theme['border'] }} rounded-2xl overflow-hidden">
                            
                            {{-- Commodity Name --}}
                            <td class="px-6 py-5 font-black text-slate-900 text-base rounded-l-2xl border-l border-t border-b {{ $theme['border'] }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $theme['icon_bg'] }} border flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                        🚚
                                    </div>
                                    <span class="truncate">{{ $shipment->harvest->commodity ?? 'N/A' }}</span>
                                </div>
                            </td>

                            {{-- Origin --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <div class="flex items-center gap-2 text-slate-700 font-bold text-sm">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    <span class="truncate">{{ $shipment->origin }}</span>
                                </div>
                            </td>

                            {{-- Destination --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <div class="flex items-center gap-2 text-slate-700 font-bold text-sm">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                    </svg>
                                    <span class="truncate">{{ $shipment->destination }}</span>
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-5 border-t border-b {{ $theme['border'] }}">
                                <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-sm
                                    @if($shipment->status == 'Delivered') bg-emerald-100 text-emerald-800 border-emerald-200
                                    @elseif($shipment->status == 'In Transit') bg-teal-100 text-teal-800 border-teal-200
                                    @elseif($shipment->status == 'Packed') bg-indigo-100 text-indigo-800 border-indigo-200
                                    @else bg-amber-100 text-amber-800 border-amber-200 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 
                                        @if($shipment->status == 'Delivered') bg-emerald-500
                                        @elseif($shipment->status == 'In Transit') bg-teal-500
                                        @elseif($shipment->status == 'Packed') bg-indigo-500
                                        @else bg-amber-500 @endif"></span>
                                    {{ $shipment->status }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5 text-right rounded-r-2xl border-r border-t border-b {{ $theme['border'] }}">
                                <div class="flex justify-end items-center gap-2">

                                    {{-- View Detail Button --}}
<a href="{{ route('shipments.show', $shipment->id) }}"
   class="p-2.5 bg-white/90 text-slate-500 rounded-xl hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all border border-slate-200/80 shadow-sm"
   title="View Shipment Detail">

    <svg class="w-4 h-4"
         fill="none"
         stroke="currentColor"
         viewBox="0 0 24 24">

        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
        </path>

        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
        </path>
    </svg>
</a>
                                    
                                    {{-- Edit Status Button --}}
                                    <button type="button" 
                                            onclick="openEditModal('{{ route('shipments.update', $shipment->id) }}', '{{ $shipment->status }}')" 
                                            class="p-2.5 bg-white/90 text-slate-500 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all border border-slate-200/80 shadow-sm"
                                            title="Update Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    {{-- Delete Button --}}
                                   <form id="deleteShipmentForm-{{ $shipment->id }}"
      action="{{ route('shipments.destroy', $shipment->id) }}"
      method="POST">
    @csrf
    @method('DELETE')

    <button type="button"
            onclick="openDeleteShipmentModal(
                '{{ $shipment->id }}',
                @js($shipment->harvest->commodity ?? 'Shipment'),
                @js($shipment->origin),
                @js($shipment->destination)
            )"
            class="p-2.5 bg-white/90 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all border border-slate-200/80 shadow-sm"
            title="Delete Shipment">

        <svg class="w-4 h-4"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
            </path>
        </svg>
    </button>
</form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center bg-slate-50/50 rounded-2xl border border-slate-200/80">
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto text-xl shadow-sm">
                                        🚚
                                    </div>
                                    <p class="text-slate-600 font-bold text-sm">No shipment data available.</p>
                                    <p class="text-slate-400 text-xs">Create your first shipment logistics route by clicking the button above.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Update Status Modal --}}
<div id="editModal"
     class="fixed inset-0 z-[9990] hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">

    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative overflow-hidden animate-card">

        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">

            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </div>

                <div>
                    <p class="text-[9px] uppercase tracking-[0.18em] font-black text-indigo-500">
                        Shipment Lifecycle
                    </p>

                    <h2 class="font-black text-slate-900 text-lg">
                        Update Status
                    </h2>
                </div>
            </div>

            <button type="button"
                    onclick="closeEditModal()"
                    class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

        </div>

        <form id="editForm"
              method="POST"
              class="space-y-5"
              onsubmit="return handleShipmentStatusSubmit(event)">

            @csrf
            @method('PATCH')

            <div>
                <label for="statusSelect"
                       class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                    Select New Shipment Status
                </label>

                <select id="statusSelect"
                        name="status"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">

                    <option value="Harvested">Harvested</option>
                    <option value="Packed">Packed</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                </select>

                <div class="mt-3 flex items-start gap-2.5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>

                    <p class="text-[11px] leading-relaxed text-slate-500">
                        Marking a shipment as Delivered closes active analysis,
                        route optimization, condition updates, and Digital Twin
                        actions. Historical evidence remains archived.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 pt-2">

                <button type="button"
                        onclick="closeEditModal()"
                        class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-2xl font-extrabold text-xs transition-colors">
                    Cancel
                </button>

                <button type="submit"
                        class="w-1/2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white py-3.5 rounded-2xl font-extrabold text-xs transition-all shadow-md shadow-indigo-600/20">
                    Save Status
                </button>

            </div>
        </form>
    </div>
</div>


{{-- Delivered Lifecycle Confirmation Modal --}}
<div id="deliveredConfirmModal"
     class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">

    <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-card">

        <div class="p-7 sm:p-8">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700">

                    <svg class="h-6 w-6"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7">
                        </path>
                    </svg>

                </div>

                <div class="min-w-0">

                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700">
                        Complete Shipment
                    </p>

                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-900">
                        Mark as Delivered?
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        This shipment will leave the active operational decision
                        cycle and move into the Completed Shipments archive.
                    </p>

                </div>
            </div>


            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">

                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    What happens next
                </p>

                <div class="mt-4 space-y-3">

                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-3 w-3"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>

                        <p class="text-xs leading-5 text-slate-600">
                            Existing analyses and recorded shipment conditions remain archived.
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-3 w-3"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>

                        <p class="text-xs leading-5 text-slate-600">
                            A final operational snapshot is retained for completed-shipment review.
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                            <svg class="h-3 w-3"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2.5"
                                      d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </div>

                        <p class="text-xs leading-5 text-slate-600">
                            New analysis, condition updates, route optimization,
                            and Digital Twin scenarios will be closed.
                        </p>
                    </div>

                </div>

            </div>


            <div class="mt-7 flex flex-col-reverse sm:flex-row gap-3">

                <button type="button"
                        onclick="closeDeliveredConfirmModal()"
                        class="sm:w-1/2 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50">
                    Keep Active
                </button>

                <button type="button"
                        onclick="confirmDeliveredShipment()"
                        class="sm:w-1/2 rounded-2xl bg-emerald-700 px-4 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                    Mark as Delivered
                </button>

            </div>

        </div>

    </div>
</div>


{{-- Delete Shipment Confirmation Modal --}}
<div id="deleteShipmentModal"
     class="fixed inset-0 z-[10010] hidden items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">

    <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-card">

        <div class="p-7 sm:p-8">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600">

                    <svg class="h-5 w-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>

                </div>

                <div>

                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-600">
                        Delete Shipment
                    </p>

                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-900">
                        Remove this shipment?
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        You are about to permanently remove
                        <span id="deleteShipmentCommodity"
                              class="font-bold text-slate-700">
                            this shipment
                        </span>.
                    </p>

                </div>

            </div>


            <div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50/60 p-4">

                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-rose-500">
                    Shipment Route
                </p>

                <p id="deleteShipmentRoute"
                   class="mt-2 text-sm font-bold text-slate-800">
                    —
                </p>

                <p class="mt-2 text-xs leading-5 text-slate-500">
                    This action is intended for incorrect or disposable records.
                    Delivered shipments should normally remain in the Completed
                    Shipments archive instead of being deleted.
                </p>

            </div>


            <div class="mt-7 flex gap-3">

                <button type="button"
                        onclick="closeDeleteShipmentModal()"
                        class="w-1/2 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button type="button"
                        onclick="confirmDeleteShipment()"
                        class="w-1/2 rounded-2xl bg-rose-600 px-4 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-700">
                    Delete Shipment
                </button>

            </div>

        </div>

    </div>
</div>


{{-- Modal Handler Scripts --}}
<script>
    let pendingDeleteShipmentId = null;


    function openEditModal(url, currentStatus) {
        const form = document.getElementById('editForm');
        const statusSelect = document.getElementById('statusSelect');

        form.action = url;

        if (currentStatus) {
            statusSelect.value = currentStatus;
        }

        document.getElementById('editModal').style.display = 'flex';
    }


    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }


    function handleShipmentStatusSubmit(event) {
        const status = document.getElementById('statusSelect').value;

        if (status !== 'Delivered') {
            return true;
        }

        event.preventDefault();

        document.getElementById('deliveredConfirmModal').style.display = 'flex';

        return false;
    }


    function closeDeliveredConfirmModal() {
        document.getElementById('deliveredConfirmModal').style.display = 'none';
    }


    function confirmDeliveredShipment() {
        const form = document.getElementById('editForm');

        closeDeliveredConfirmModal();

        /*
         * Bypass the form's onsubmit handler so the confirmation
         * modal does not open for a second time.
         */
        HTMLFormElement.prototype.submit.call(form);
    }


    function openDeleteShipmentModal(id, commodity, origin, destination) {
        pendingDeleteShipmentId = id;

        document.getElementById('deleteShipmentCommodity').textContent =
            commodity || 'this shipment';

        document.getElementById('deleteShipmentRoute').textContent =
            `${origin || 'Unknown origin'} → ${destination || 'Unknown destination'}`;

        document.getElementById('deleteShipmentModal').style.display = 'flex';
    }


    function closeDeleteShipmentModal() {
        pendingDeleteShipmentId = null;

        document.getElementById('deleteShipmentModal').style.display = 'none';
    }


    function confirmDeleteShipment() {
        if (!pendingDeleteShipmentId) {
            return;
        }

        const form = document.getElementById(
            `deleteShipmentForm-${pendingDeleteShipmentId}`
        );

        if (!form) {
            closeDeleteShipmentModal();
            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    }


    window.addEventListener('click', function (event) {
        const editModal = document.getElementById('editModal');
        const deliveredModal = document.getElementById('deliveredConfirmModal');
        const deleteModal = document.getElementById('deleteShipmentModal');

        if (event.target === deliveredModal) {
            closeDeliveredConfirmModal();
            return;
        }

        if (event.target === deleteModal) {
            closeDeleteShipmentModal();
            return;
        }

        if (event.target === editModal) {
            closeEditModal();
        }
    });


    window.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        const deliveredModal = document.getElementById('deliveredConfirmModal');
        const deleteModal = document.getElementById('deleteShipmentModal');
        const editModal = document.getElementById('editModal');

        if (deliveredModal.style.display === 'flex') {
            closeDeliveredConfirmModal();
            return;
        }

        if (deleteModal.style.display === 'flex') {
            closeDeleteShipmentModal();
            return;
        }

        if (editModal.style.display === 'flex') {
            closeEditModal();
        }
    });
</script>
</x-app-layout>