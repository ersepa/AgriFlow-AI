<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @livewireStyles
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AgriFlow AI') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

        <style>
            /* Design System Overlay & Custom Styles derived from Homepage */
            .agri-sidebar-link {
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .agri-sidebar-link:hover {
                transform: translateX(4px);
            }
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

            /* Background grid overlay */
            .bg-grid-pattern {
                background-image: radial-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px);
                background-size: 24px 24px;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f8fafc] text-slate-800 bg-grid-pattern min-h-screen overflow-x-hidden selection:bg-emerald-500 selection:text-white">

        {{-- Overlay Sidebar untuk Tampilan Seluler --}}
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

        {{-- Sidebar Layout --}}
        <aside id="sidebar" class="fixed top-0 left-0 z-50 h-screen w-72 bg-white/90 backdrop-blur-xl border-r border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(15,23,42,0.05)] lg:shadow-none lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            
            {{-- Header Sidebar (Logo & Brand) --}}
            <div class="flex items-center justify-between h-20 px-6 border-b border-slate-100">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 flex items-center justify-center p-1.5 shadow-sm group-hover:scale-105 transition-transform">
                        <img src="{{ asset('newlogo.jpeg') }}" alt="AgriFlow AI Logo" class="w-full h-full object-contain" style="mix-blend-mode: multiply;">
                    </div>
                    
                    <div class="flex flex-col">
                        <span class="text-lg font-black tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors">
                            AgriFlow<span class="text-emerald-600">AI</span>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Enterprise</span>
                    </div>
                </a>
                
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigasi Utama --}}
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">

                    {{-- Overview --}}
    <div class="px-4 pt-2 pb-1">
        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-400">
            Ringkasan
        </span>
    </div>
                
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800 shadow-sm border border-emerald-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-emerald-500 rounded-r-full transition-opacity duration-300 
                        {{ request()->routeIs('dashboard') ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}"></div>
                    
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                    {{-- Operations --}}
    <div class="px-4 pt-5 pb-1">
        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-400">
            Operasional
        </span>
    </div>


                {{-- Harvests --}}
                <a href="{{ route('harvests.index') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('harvests*') ? 'bg-emerald-50 text-emerald-800 shadow-sm border border-emerald-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-emerald-500 rounded-r-full transition-transform duration-300 
                        {{ request()->routeIs('harvests*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
                    
                    <svg class="w-5 h-5 {{ request()->routeIs('harvests*') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    <span>Hasil Panen</span>
                </a>

                {{-- Shipments --}}
                <a href="{{ route('shipments.index') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('shipments*') ? 'bg-teal-50 text-teal-800 shadow-sm border border-teal-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-teal-700' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-teal-500 rounded-r-full transition-transform duration-300 
                        {{ request()->routeIs('shipments*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
                    
                    <svg class="w-5 h-5 {{ request()->routeIs('shipments*') ? 'text-teal-600' : 'text-slate-400 group-hover:text-teal-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Pengiriman</span>
                </a>


                {{-- Completed Shipments --}}
                <a href="{{ route('completed-shipments.index') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden
                    {{ request()->routeIs('completed-shipments.*') ? 'bg-emerald-50 text-emerald-800 shadow-sm border border-emerald-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                    <div class="absolute left-0 w-1.5 h-6 bg-emerald-500 rounded-r-full transition-transform duration-300
                        {{ request()->routeIs('completed-shipments.*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
                    <svg class="w-5 h-5 {{ request()->routeIs('completed-shipments.*') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pengiriman Selesai</span>
                </a>

                    {{-- Decision Intelligence --}}
    <div class="px-4 pt-5 pb-1">
        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-400">
            Intelijen & Analisis AI
        </span>
    </div>

                {{-- AI Analysis --}}
                <a href="{{ route('ai-analysis.index') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('ai-analysis*') ? 'bg-indigo-50 text-indigo-800 shadow-sm border border-indigo-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-700' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-indigo-500 rounded-r-full transition-transform duration-300 
                        {{ request()->routeIs('ai-analysis*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
                    
                    <svg class="w-5 h-5 {{ request()->routeIs('ai-analysis*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    <span>Analisis AI</span>
                </a>

                {{-- AI Optimizer --}}
                <a href="{{ route('ai-optimizer') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('ai-optimizer*') ? 'bg-purple-50 text-purple-800 shadow-sm border border-purple-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-purple-700' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-purple-500 rounded-r-full transition-transform duration-300 
                        {{ request()->routeIs('ai-optimizer*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>

                    <svg class="w-5 h-5 {{ request()->routeIs('ai-optimizer*') ? 'text-purple-600' : 'text-slate-400 group-hover:text-purple-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3a3 3 0 013 3v1.5h1.5a3 3 0 013 3v6a3 3 0 01-3 3h-6a3 3 0 01-3-3v-6a3 3 0 013-3h1.5V6a3 3 0 013-3zM9 14h6M12 11v6"></path>
                    </svg>
                    <span>Optimasi AI</span>
                </a>

                {{-- Digital Twin --}}
                <a href="{{ route('digital-twin.index') }}" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden
                    {{ request()->routeIs('digital-twin.*') ? 'bg-cyan-50 text-cyan-800 shadow-sm border border-cyan-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-cyan-700' }}">

                    <div class="absolute left-0 w-1.5 h-6 bg-cyan-500 rounded-r-full transition-transform duration-300
                        {{ request()->routeIs('digital-twin.*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}">
                    </div>

                    <svg class="w-5 h-5 {{ request()->routeIs('digital-twin.*') ? 'text-cyan-600' : 'text-slate-400 group-hover:text-cyan-600' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M3 9h2m14 0h2M9 19v2m6-2v2M3 15h2m14 0h2M7 7h10v10H7V7z"/>
                    </svg>
                    <span>Digital Twin Operasional</span>
                </a>

                <div class="pt-4 pb-1">
                    <hr class="border-slate-100">
                </div>

                    {{-- Account --}}
    <div class="px-4 pt-2 pb-1">
        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-400">
            Akun
        </span>
    </div>

                {{-- Profile --}}
                <a href="/profile" class="agri-sidebar-link group relative flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-300 overflow-hidden 
                    {{ request()->routeIs('profile*') ? 'bg-slate-100 text-slate-900 shadow-sm border border-slate-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    
                    <div class="absolute left-0 w-1.5 h-6 bg-slate-500 rounded-r-full transition-transform duration-300 
                        {{ request()->routeIs('profile*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
                    
                    <svg class="w-5 h-5 {{ request()->routeIs('profile*') ? 'text-slate-800' : 'text-slate-400 group-hover:text-slate-700' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Pengaturan Profil</span>
                </a>

            </nav>
        </aside>

        {{-- Konten Utama --}}
        <div class="flex flex-col min-h-screen lg:pl-72 transition-all duration-300">
            
            {{-- Header Atas (Top Navigation Bar) --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_15px_-3px_rgba(15,23,42,0.03)] h-16 flex items-center justify-between px-4 lg:px-8">
                
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-500 hover:text-emerald-600 rounded-xl hover:bg-emerald-50 transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </button>
                
                <div class="flex-1 w-full flex justify-end">
                    @include('layouts.navigation')
                </div>
            </header>

            {{-- Main Slot --}}
            <main class="flex-1">
                {{ $slot }}
            </main>

        </div>

        {{-- Notification Toast --}}
        <div x-data="{ show: false, message: '' }" 
             x-init="@if(session('success')) show = true; message = '{{ session('success') }}'; setTimeout(() => show = false, 3500) @endif"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-slate-700/80"
             style="display: none;">
            
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            
            <span class="font-bold text-sm tracking-tight" x-text="message"></span>
        </div>

{{-- Floating AI Assistant Bubble --}}
        <button type="button"
                id="chatBubble"
                onclick="toggleChat()"
                aria-label="Buka Asisten AgriFlow"
                class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 p-4 rounded-2xl shadow-xl shadow-emerald-600/30 cursor-pointer hover:scale-105 transition-all duration-300 z-50 border border-emerald-400/40 text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </button>

        {{-- Jendela Chat Asisten AgriFlow --}}
        <div id="chatWindow"
             class="fixed bottom-20 right-4 sm:bottom-24 sm:right-6
             w-[calc(100vw-2rem)] sm:w-[400px]
             h-[72vh] sm:h-[540px] max-h-[680px]
             bg-white rounded-3xl
             shadow-[0_24px_60px_-12px_rgba(15,23,42,0.22)]
             border border-emerald-200/80
             overflow-hidden hidden z-50
             transition-all duration-300 ease-in-out
             flex flex-col">

            {{-- Header Chat --}}
            <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-700 p-4 text-white flex justify-between items-center border-b border-emerald-600/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-white/15 border border-white/20 text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                        </svg>
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-emerald-800"></span>
                    </div>
                    <div>
                        <h4 class="text-sm font-black tracking-tight leading-none">Asisten AgriFlow</h4>
                        <p class="text-xs text-emerald-100 font-medium mt-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            Siap membantu
                        </p>
                    </div>
                </div>

                <button type="button"
                        onclick="toggleChat()"
                        aria-label="Tutup Asisten AgriFlow"
                        class="p-2 text-emerald-100 hover:text-white hover:bg-white/15 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body Chat --}}
            <div id="chatBody"
                 class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-emerald-50/40 via-slate-50 to-white text-slate-800 text-sm leading-relaxed custom-scrollbar">

                {{-- Sapaan Awal --}}
                <div class="flex gap-2.5 items-start">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                        </svg>
                    </div>
                    <div class="bg-white border border-emerald-200/80 p-3.5 rounded-2xl rounded-tl-sm shadow-2xs text-slate-800 text-sm font-medium max-w-[85%] leading-relaxed">
                        Halo! Saya Asisten AgriFlow. Ada yang bisa saya bantu mengenai hasil panen, pengiriman, atau analisis risiko hari ini?
                    </div>
                </div>

                {{-- Saran Pertanyaan Awal --}}
                <div id="chatSuggestions" class="pl-10 flex flex-wrap gap-2">
                    <button type="button" onclick="sendQuickQuestion('Bagaimana kondisi hasil panen saya?')"
                            class="text-left px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold transition-colors">
                        Bagaimana kondisi hasil panen saya?
                    </button>
                    <button type="button" onclick="sendQuickQuestion('Jelaskan risiko pengiriman saya.')"
                            class="text-left px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold transition-colors">
                        Jelaskan risiko pengiriman saya.
                    </button>
                    <button type="button" onclick="sendQuickQuestion('Apa rekomendasi AI untuk pengiriman?')"
                            class="text-left px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold transition-colors">
                        Apa rekomendasi AI untuk pengiriman?
                    </button>
                    <button type="button" onclick="sendQuickQuestion('Bagaimana cara menjaga kualitas produk?')"
                            class="text-left px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold transition-colors">
                        Bagaimana cara menjaga kualitas produk?
                    </button>
                </div>
            </div>

            {{-- Input Chat --}}
            <div class="p-3.5 bg-white border-t border-slate-200/80 flex gap-2 items-center shrink-0">
                <input id="chatInput"
                       type="text"
                       placeholder="Tulis pertanyaan kamu..."
                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); sendMessage(); }"
                       class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all">

                <button type="button"
                        id="chatSendBtn"
                        onclick="sendMessage()"
                        aria-label="Kirim pesan"
                        class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed text-white p-3 rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Script Handlers --}}
        <script>
        let isChatSending = false;

        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            chatWindow.classList.toggle('hidden');
            if (!chatWindow.classList.contains('hidden')) {
                setTimeout(() => document.getElementById('chatInput')?.focus(), 100);
            }
        }

        function escapeChatHtml(text) {
            return String(text ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
                .replace(/\n/g, '<br>');
        }

        function sendQuickQuestion(text) {
            const input = document.getElementById('chatInput');
            if (!input || isChatSending) return;
            input.value = text;
            sendMessage();
        }

        async function sendMessage() {
            if (isChatSending) return;

            const input = document.getElementById('chatInput');
            const sendBtn = document.getElementById('chatSendBtn');
            const chatBody = document.getElementById('chatBody');
            const suggestions = document.getElementById('chatSuggestions');
            const message = input.value.trim();

            if (!message) return;

            isChatSending = true;
            input.disabled = true;
            if (sendBtn) sendBtn.disabled = true;
            if (suggestions) suggestions.classList.add('hidden');

            // Tampilkan pesan pengguna (Hijau Emerald)
            chatBody.insertAdjacentHTML('beforeend', `
                <div class="flex justify-end">
                    <div class="bg-emerald-600 text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm font-medium shadow-sm max-w-[82%] leading-relaxed">
                        ${escapeChatHtml(message)}
                    </div>
                </div>
            `);

            input.value = '';

            // Indikator loading mengetik
            const typingId = 'chatTyping-' + Date.now();
            chatBody.insertAdjacentHTML('beforeend', `
                <div id="${typingId}" class="flex gap-2.5 items-start">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                        </svg>
                    </div>
                    <div class="bg-white border border-emerald-200/80 px-4 py-3 rounded-2xl rounded-tl-sm shadow-2xs text-slate-600 text-xs font-semibold flex items-center gap-2">
                        <span class="inline-flex gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce" style="animation-delay:0.15s"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce" style="animation-delay:0.3s"></span>
                        </span>
                        <span>Sedang menyiapkan jawaban...</span>
                    </div>
                </div>
            `);

            chatBody.scrollTop = chatBody.scrollHeight;

            try {
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: message })
                });

                document.getElementById(typingId)?.remove();

                let data = {};
                try {
                    data = await response.json();
                } catch (_) {
                    data = {};
                }

                const replyText = data.reply
                    ?? (response.ok
                        ? 'Maaf, asisten belum dapat menyiapkan jawaban.'
                        : 'Maaf, asisten sedang mengalami kendala. Silakan coba kembali beberapa saat lagi.');

                const isError = !response.ok;
                const bubbleStyle = isError
                    ? 'bg-rose-50 border-rose-200 text-rose-800'
                    : 'bg-white border-emerald-200/80 text-slate-800';

                chatBody.insertAdjacentHTML('beforeend', `
                    <div class="flex gap-2.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>
                            </svg>
                        </div>
                        <div class="${bubbleStyle} border px-4 py-3 rounded-2xl rounded-tl-sm shadow-2xs text-sm font-medium max-w-[85%] leading-relaxed">
                            ${escapeChatHtml(replyText)}
                        </div>
                    </div>
                `);
            } catch (error) {
                console.error(error);
                document.getElementById(typingId)?.remove();
                chatBody.insertAdjacentHTML('beforeend', `
                    <div class="flex gap-2.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">!</div>
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl rounded-tl-sm text-sm font-semibold max-w-[85%] leading-relaxed">
                            Maaf, koneksi ke asisten terputus. Silakan periksa jaringan dan coba kembali.
                        </div>
                    </div>
                `);
            } finally {
                isChatSending = false;
                input.disabled = false;
                if (sendBtn) sendBtn.disabled = false;
                input.focus();
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            
            if (overlay.classList.contains('hidden')) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
        </script>

        @livewireScripts
    </body>
</html>