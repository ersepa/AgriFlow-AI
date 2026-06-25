<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @livewireStyles
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AgriFlow AI') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"
/>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 overflow-x-hidden">

        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

        <aside id="sidebar" class="fixed top-0 left-0 z-50 h-screen w-72 bg-white/80 backdrop-blur-xl border-r border-slate-100 shadow-2xl lg:shadow-none lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            
<div class="flex items-center justify-between h-20 px-6 border-b border-slate-100/50">
    <div class="flex items-center gap-3">
        <!-- Logo Image -->
        <div class="w-20 h-20 overflow-hidden">
            <img src="{{ asset('logo.png') }}" alt="AgriFlow AI Logo" class="w-full h-full object-contain" style="mix-blend-mode: multiply;">
        </div>
        
        <!-- Text -->
        <span class="text-xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-slate-500">
            AgriFlow AI
        </span>
    </div>
    
    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">

                <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="font-medium">Home</span>
    </a>
                
<a href="{{ route('dashboard') }}" class="group relative flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 overflow-hidden 
    {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-600 font-bold' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
    
    <div class="absolute left-0 w-1.5 h-8 bg-emerald-500 rounded-r-full transition-opacity duration-300 
        {{ request()->routeIs('dashboard') ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}"></div>
    
    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
    </svg>
    
    <span class="group-hover:translate-x-1 transition-transform duration-300">Dashboard</span>
</a>

<a href="/harvests" class="group relative flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 overflow-hidden 
    {{ request()->routeIs('harvests*') ? 'bg-emerald-50 text-emerald-600 font-bold' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
    
    <div class="absolute left-0 w-1.5 h-8 bg-emerald-500 rounded-r-full transition-transform duration-300 
        {{ request()->routeIs('harvests*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
    
    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
    <span class="group-hover:translate-x-1 transition-transform duration-300">Harvests</span>
</a>

<a href="/shipments" class="group relative flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 overflow-hidden 
    {{ request()->routeIs('shipments*') ? 'bg-emerald-50 text-emerald-600 font-bold' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
    
    <div class="absolute left-0 w-1.5 h-8 bg-emerald-500 rounded-r-full transition-transform duration-300 
        {{ request()->routeIs('shipments*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
    
    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
    <span class="group-hover:translate-x-1 transition-transform duration-300">Shipments</span>
</a>

<a href="/ai-analysis" class="group relative flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 overflow-hidden 
    {{ request()->routeIs('ai-analysis*') ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-500 hover:bg-indigo-50 hover:text-indigo-600' }}">
    
    <div class="absolute left-0 w-1.5 h-8 bg-indigo-500 rounded-r-full transition-transform duration-300 
        {{ request()->routeIs('ai-analysis*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
    
    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
    <span class="group-hover:translate-x-1 transition-transform duration-300">AI Analysis</span>
</a>

                <hr class="border-slate-100 my-4">

<a href="/profile" class="group relative flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 overflow-hidden 
    {{ request()->routeIs('profile*') ? 'bg-slate-200 text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
    
    <div class="absolute left-0 w-1.5 h-8 bg-slate-500 rounded-r-full transition-transform duration-300 
        {{ request()->routeIs('profile*') ? 'translate-x-0' : '-translate-x-full group-hover:translate-x-0' }}"></div>
    
    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
    </svg>
    
    <span class="group-hover:translate-x-1 transition-transform duration-300">Profile</span>
</a>

            </nav>
        </aside>

        <div class="flex flex-col min-h-screen lg:pl-72 transition-all duration-300">
            
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm h-16 flex items-center justify-between px-4 lg:px-8">
                
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-500 hover:text-emerald-600 rounded-xl hover:bg-emerald-50 transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </button>
                
                <div class="flex-1 w-full flex justify-end">
                    @include('layouts.navigation')
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

        </div>
        <div x-data="{ show: false, message: '' }" 
     x-init="@if(session('success')) show = true; message = '{{ session('success') }}'; setTimeout(() => show = false, 3000) @endif"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700">
    
    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
    </svg>
    
    <span class="font-bold tracking-tight" x-text="message"></span>
</div>
<div id="chatBubble" onclick="toggleChat()" 
     class="fixed bottom-6 right-6 bg-indigo-600 p-4 rounded-full shadow-lg cursor-pointer hover:bg-indigo-700 transition-all z-50">
    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
    </svg>
</div>

<!-- Jendela Chat (Versi Elegant & Solid) -->
<div id="chatWindow"
    class="fixed bottom-4 right-4 sm:bottom-20 sm:right-6 md:right-8
    w-[calc(100vw-2rem)] sm:w-96
    h-[70vh] sm:h-[500px]
    max-h-[700px]
    bg-slate-50 rounded-2xl
    shadow-[0_15px_50px_rgba(0,0,0,0.25)]
    border-2 border-slate-800
    overflow-hidden hidden z-50
    transition-all duration-300 ease-in-out
    flex flex-col">

    <!-- Header -->
    <div class="bg-slate-800 p-4 text-white font-semibold flex justify-between items-center border-b-2 border-slate-900">
        <span class="flex items-center gap-2">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            Asisten Logistik
        </span>

        <button onclick="toggleChat()" class="hover:text-slate-300 transition-colors">
            ✕
        </button>
    </div>

    <!-- Body -->
<div id="chatBody"
    class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 bg-white text-slate-700 text-sm leading-relaxed border-b border-slate-200"
    style="white-space: pre-line;">
        <p class="text-slate-500 italic">
            Halo! Ada yang bisa dibantu soal data pengiriman hari ini?
        </p>
    </div>

    <!-- Input -->
    <div class="p-3 sm:p-4 bg-slate-50 flex gap-2">
        <input id="chatInput"
            type="text"
            placeholder="Tanya sesuatu..."
            class="flex-1 p-3 border-2 border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all">

        <button onclick="sendMessage()"
            class="bg-slate-800 text-white px-4 sm:px-5 py-2 rounded-xl text-sm font-bold hover:bg-slate-900 transition-all">
            Kirim
        </button>
    </div>
</div>

<script>
    function toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.toggle('hidden');
    }

    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const chatBody = document.getElementById('chatBody');
        const message = input.value;
        if (!message) return;

        // Tambah pesan user
        chatBody.innerHTML += `<div class="text-right"><span class="bg-indigo-100 p-2 rounded-lg inline-block">${message}</span></div>`;
        input.value = '';

        // Kirim ke Controller
        const response = await fetch('/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: message })
        });
        const data = await response.json();

        // Tambah jawaban AI
        chatBody.innerHTML += `<div class="text-left"><span class="bg-white border p-2 rounded-lg inline-block shadow-sm">${data.reply}</span></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }
</script>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                
                // Toggle posisi sidebar (masuk/keluar layar)
                sidebar.classList.toggle('-translate-x-full');
                
                // Toggle overlay hitam transparan
                if (overlay.classList.contains('hidden')) {
                    overlay.classList.remove('hidden');
                    // setTimeout dikit biar transisi opacity-nya kelihatan
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                } else {
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300); // tunggu transisi kelar baru di-hide
                }
            }
        </script>
        @livewireScripts
    </body>
</html>