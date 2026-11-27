<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriFlow AI - Kecerdasan Digital untuk Pengelolaan Pascapanen</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 6s ease-in-out 3s infinite;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }

        /* Grid Background Pattern */
        .bg-grid-pattern {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Subtle Dot Pattern for Dark Sections */
        .bg-dot-dark {
            background-image: radial-gradient(circle, rgba(148, 163, 184, 0.15) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 overflow-x-hidden selection:bg-emerald-500 selection:text-white">

    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden bg-grid-pattern opacity-40"></div>
    <div class="fixed top-[-10%] left-[-10%] w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-50 z-0 animate-pulse"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-indigo-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 z-0 animate-pulse" style="animation-duration: 4s;"></div>

    <div class="relative z-10 min-h-screen flex flex-col">
        
        {{-- ================= NAVBAR ================= --}}
        <header class="py-6 px-6 lg:px-12 flex justify-between items-center animate-fade-in-up">
            <div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center shadow-lg shadow-emerald-200/50">
    <img
        src="{{ asset('newlogo.jpeg') }}"
        alt="AgriFlow AI Logo"
        class="w-full h-full object-contain"
    >
</div>
                <span class="text-2xl font-black tracking-tight text-slate-800">
                    AgriFlow<span class="text-emerald-600">AI</span>
                </span>
            </div>

            @if (Route::has('login'))
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-slate-600 hover:text-emerald-600 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:block font-bold text-slate-600 hover:text-emerald-600 transition-colors">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="relative inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white transition-all duration-300 bg-slate-900 rounded-full hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900">
                                Mulai Sekarang
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </header>

        {{-- ================= HERO SECTION ================= --}}
        <main class="flex-1 flex flex-col lg:flex-row items-center justify-center px-6 lg:px-12 py-12 gap-16 max-w-7xl mx-auto w-full">
            
            <div class="flex-1 text-center lg:text-left z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold mb-6 animate-fade-in-up">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    Kecerdasan Digital untuk Pengelolaan Pascapanen
                </div>

                <h1 class="text-5xl lg:text-6xl xl:text-7xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight animate-fade-in-up delay-100">
                    Pastikan Hasil Panen <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-indigo-600">
                        Tetap Terjaga Hingga Tujuan.
                    </span>
                </h1>

                <p class="text-lg text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed animate-fade-in-up delay-200 font-medium">
                    AgriFlow AI membantu memantau kondisi produk, menilai risiko pengiriman, memprediksi kualitas saat tiba, dan menentukan langkah terbaik untuk menjaga hasil panen.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start animate-fade-in-up delay-300">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto relative group inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-300 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl hover:shadow-xl hover:shadow-emerald-500/30 hover:-translate-y-1 overflow-hidden">
                        <div class="absolute inset-0 w-full h-full -x-10 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-150%] group-hover:translate-x-[150%] transition-transform duration-700 ease-in-out"></div>
                        Buat Akun
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-2xl hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 transition-all duration-300 hover:-translate-y-1">
                        Masuk
                    </a>
                </div>
            </div>

            {{-- Ilustrasi Hero --}}
            <div class="flex-1 relative w-full max-w-lg lg:max-w-none animate-fade-in-up delay-200 hidden md:block">
                <div class="relative bg-white/80 backdrop-blur-2xl border border-slate-100 p-6 rounded-3xl shadow-2xl animate-float z-20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm font-bold text-slate-500">Penilaian Pengiriman</p>
                            <p class="text-2xl font-black text-slate-800">Pengiriman Layak Dilakukan</p>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-2xl">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                    <div class="space-y-3.5">
                        <div>
                            <p class="text-xs font-bold text-slate-600 mb-1.5">Kondisi Produk</p>
                            <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 w-3/4 rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-600 mb-1.5">Kelayakan Rute</p>
                            <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 w-1/2 rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-600 mb-1.5">Risiko Pengiriman</p>
                            <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-rose-500 w-1/4 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-8 -left-8 bg-white border border-slate-100 p-5 rounded-3xl shadow-xl animate-float-delayed z-30">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-100 rounded-2xl">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Fokus Analisis</p>
                            <p class="text-xl font-black text-slate-800">Pascapanen</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -top-10 -right-8 bg-gradient-to-br from-indigo-500 to-blue-600 p-5 rounded-3xl shadow-xl shadow-indigo-500/20 animate-float z-10">
                    <p class="text-xs font-bold text-indigo-100 uppercase mb-1">
                        Mesin Pengambilan Keputusan
                    </p>
                    <p class="text-xl font-black text-white">
                        Terukur &amp; Akurat
                    </p>
                </div>
                
                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-100 to-indigo-50 rounded-[3rem] transform rotate-3 scale-105 -z-10 transition-transform hover:rotate-6 duration-500"></div>
            </div>
            
        </main>

        {{-- =====================================================================
             SECTION A: APA YANG DILAKUKAN AGRIFLOW (Soft Green Background)
        ===================================================================== --}}
        <section class="relative py-24 bg-gradient-to-b from-emerald-50/80 via-white to-white border-t border-emerald-100/80">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    {{-- Left: Storytelling & Value Proposition --}}
                    <div class="lg:col-span-5 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22c4-4 8-7.5 8-12a8 8 0 1 0-16 0c0 4.5 4 8 8 12Z"/><circle cx="12" cy="10" r="3"/></svg>
                            Mengenal AgriFlow AI
                        </div>

                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight tracking-tight">
                            Satu Platform untuk Menjaga Kesegaran Hasil Panen Hingga Tujuan
                        </h2>

                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                            AgriFlow menghubungkan catatan panen, daya tahan produk, kondisi pengiriman, dan pilihan rute dalam satu tampilan yang mudah dipahami. Anda bisa mengetahui risiko pengiriman lebih awal sebelum produk berangkat.
                        </p>

                        <div class="pt-2 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Data panen dan pengiriman tercatat rapi di satu tempat.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex-shrink-0 w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Rute dinilai berdasarkan waktu tempuh dan batas kesegaran produk.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex-shrink-0 w-5 h-5 rounded-full bg-purple-500 text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Bantu mengambil keputusan cepat sebelum kualitas hasil panen turun.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: 4 Color-Coded Pillar Cards (2x2 Staggered Grid) --}}
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Card 1: Green (Panen) --}}
                        <div class="p-6 rounded-3xl bg-emerald-50 border border-emerald-200/80 shadow-sm hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 mb-5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Informasi Hasil Panen</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Catat jenis komoditas, berat panen, tanggal panen, dan perkiraan masa simpan sebagai dasar perencanaan kirim.
                            </p>
                        </div>

                        {{-- Card 2: Purple (Analisis Kesegaran AI) --}}
                        <div class="p-6 rounded-3xl bg-purple-50 border border-purple-200/80 shadow-sm hover:shadow-lg hover:shadow-purple-500/10 hover:-translate-y-1 transition-all duration-300 sm:mt-6">
                            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-600/20 mb-5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Prediksi Kesegaran Produk</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Ketahui perkiraan kondisi produk saat tiba di tujuan beserta sisa masa simpannya berdasarkan jenis komoditas.
                            </p>
                        </div>

                        {{-- Card 3: Blue (Rute Pengiriman) --}}
                        <div class="p-6 rounded-3xl bg-blue-50 border border-blue-200/80 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20 mb-5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 18H9"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Evaluasi Rute &amp; Waktu</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Pastikan waktu tempuh perjalanan masih aman dibandingkan dengan batas ketahanan produk selama di jalan.
                            </p>
                        </div>

                        {{-- Card 4: Amber/Orange (Peringatan & Kondisi) --}}
                        <div class="p-6 rounded-3xl bg-amber-50 border border-amber-200/80 shadow-sm hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 sm:mt-6">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 mb-5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.87L13.637 3.59a1.914 1.914 0 0 0-3.274 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Deteksi Risiko Lebih Awal</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Identifikasi potensi hambatan pengiriman sejak dini agar Anda punya cukup waktu untuk menyesuaikan rencana.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =====================================================================
             SECTION B: MASALAH DISTRIBUSI PERTANIAN (Dark Navy Section)
        ===================================================================== --}}
        <section class="relative py-24 bg-slate-900 text-white overflow-hidden bg-dot-dark">
            {{-- Subtle Ambient Glows --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12">
                <div class="max-w-3xl mb-16">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-xs font-extrabold uppercase tracking-wider mb-5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        Tantangan di Lapangan
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-5">
                        Mengapa Pengiriman Hasil Panen Sering Bermasalah?
                    </h2>
                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                        Hasil pertanian berbeda dengan barang biasa. Tanpa perencanaan yang mempertimbangkan kesegaran dan waktu tempuh, kualitas produk bisa menurun sebelum sampai ke pembeli.
                    </p>
                </div>

                {{-- 4 Problem Blocks in Clean 2x2 Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Problem 1 --}}
                    <div class="group p-7 rounded-3xl bg-slate-800/80 border border-slate-700/80 hover:border-amber-400/50 transition-all duration-300 flex gap-5 items-start">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-amber-500/15 border border-amber-400/30 text-amber-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Kesegaran Cepat Menurun di Jalan</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">
                                Produk pertanian mudah terpengaruh oleh suhu, penanganan, dan kondisi kendaraan. Tanpa pemantauan yang tepat, kesegaran bisa berkurang drastis selama pengiriman.
                            </p>
                        </div>
                    </div>

                    {{-- Problem 2 --}}
                    <div class="group p-7 rounded-3xl bg-slate-800/80 border border-slate-700/80 hover:border-blue-400/50 transition-all duration-300 flex gap-5 items-start">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-blue-500/15 border border-blue-400/30 text-blue-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Waktu Tempuh Menggerus Kualitas</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">
                                Setiap komoditas punya batas waktu aman setelah panen. Perjalanan yang terlalu lama atau tertunda dapat mempersempit sisa masa simpan saat produk tiba.
                            </p>
                        </div>
                    </div>

                    {{-- Problem 3 --}}
                    <div class="group p-7 rounded-3xl bg-slate-800/80 border border-slate-700/80 hover:border-purple-400/50 transition-all duration-300 flex gap-5 items-start">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-purple-500/15 border border-purple-400/30 text-purple-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Rute Terdekat Belum Tentu Terbaik</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">
                                Memilih rute hanya berdasarkan jarak tidak selalu aman. Durasi perjalanan, kelayakan jalur, dan ketahanan produk perlu dinilai secara bersamaan.
                            </p>
                        </div>
                    </div>

                    {{-- Problem 4 --}}
                    <div class="group p-7 rounded-3xl bg-slate-800/80 border border-slate-700/80 hover:border-emerald-400/50 transition-all duration-300 flex gap-5 items-start">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16h.01"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Keputusan Terlambat Sulit Diperbaiki</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">
                                Jika masalah baru disadari saat barang sudah di perjalanan, pilihan untuk mengganti rute, kendaraan, atau jadwal menjadi jauh lebih terbatas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Bottom Highlight Banner inside Dark Section --}}
                <div class="mt-10 p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-emerald-600/90 via-teal-600/90 to-blue-600/90 border border-white/15 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4 text-center md:text-left">
                        <div class="hidden sm:flex w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-sm items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-lg sm:text-xl font-extrabold text-white">Cegah Masalah Sebelum Pengiriman Berangkat</h4>
                            <p class="text-emerald-50 text-sm mt-1">AgriFlow membantu Anda melihat risiko dan membandingkan opsi terbaik sejak tahap perencanaan.</p>
                        </div>
                    </div>
                    <a href="#cara-kerja" class="flex-shrink-0 px-6 py-3 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-emerald-50 transition-colors shadow-md">
                        Lihat Cara Kerjanya
                    </a>
                </div>
            </div>
        </section>

        {{-- =====================================================================
             SECTION C: CARA KERJA AGRIFLOW (Light Blue Background)
        ===================================================================== --}}
        <section id="cara-kerja" class="py-24 bg-gradient-to-b from-blue-50/70 via-sky-50/40 to-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100 text-blue-800 text-xs font-extrabold uppercase tracking-wider mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m-7-7 7 7-7 7"/></svg>
                        Alur Kerja Sederhana
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-4">
                        Cara Kerja AgriFlow dalam 4 Tahap
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Dari pencatatan hasil panen hingga simulasi skenario pengiriman, semua disusun agar mudah diikuti oleh siapa saja.
                    </p>
                </div>

                {{-- Connected Horizontal Workflow --}}
                <div class="relative">
                    {{-- Connecting Line (Desktop Only) --}}
                    <div class="hidden lg:block absolute top-14 left-[12%] right-[12%] h-1 bg-gradient-to-r from-emerald-300 via-purple-300 via-blue-300 to-amber-300 rounded-full z-0"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative z-10">
                        {{-- Stage 1 --}}
                        <div class="group bg-white rounded-3xl p-7 border border-emerald-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect width="6" height="4" x="9" y="3" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 16h6"/></svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Tahap 01</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Catat Panen &amp; Pengiriman</h3>
                            <p class="text-sm text-slate-600 leading-relaxed flex-1">
                                Masukkan informasi hasil panen, jenis komoditas, lokasi asal, tujuan, serta detail pengiriman yang direncanakan.
                            </p>
                        </div>

                        {{-- Stage 2 --}}
                        <div class="group bg-white rounded-3xl p-7 border border-purple-100 shadow-sm hover:shadow-xl hover:shadow-purple-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-lg shadow-purple-600/25 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-5 5-4-4-3 3"/></svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">Tahap 02</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Analisis Kondisi Produk</h3>
                            <p class="text-sm text-slate-600 leading-relaxed flex-1">
                                Sistem mengevaluasi kesegaran produk, sisa masa simpan, serta potensi risiko selama pengiriman berlangsung.
                            </p>
                        </div>

                        {{-- Stage 3 --}}
                        <div class="group bg-white rounded-3xl p-7 border border-blue-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/25 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="6" cy="19" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">Tahap 03</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Evaluasi Rute Pengiriman</h3>
                            <p class="text-sm text-slate-600 leading-relaxed flex-1">
                                Bandingkan durasi perjalanan, kelayakan rute, dan batas kesegaran produk untuk memilih jalur yang paling aman.
                            </p>
                        </div>

                        {{-- Stage 4 --}}
                        <div class="group bg-white rounded-3xl p-7 border border-amber-100 shadow-sm hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/25 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Tahap 04</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 mb-2">Bandingkan Skenario Operasional</h3>
                            <p class="text-sm text-slate-600 leading-relaxed flex-1">
                                Gunakan simulasi Digital Twin untuk membandingkan rencana saat ini dengan alternatif rute, kendaraan, atau kondisi suhu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =====================================================================
             SECTION D: MANFAAT AGRIFLOW (White Background + Bento Grid)
        ===================================================================== --}}
        <section class="py-24 bg-white border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-14">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-100 text-purple-800 text-xs font-extrabold uppercase tracking-wider mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            Manfaat Nyata
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight">
                            Mengapa Menggunakan AgriFlow untuk Operasional Anda?
                        </h2>
                    </div>
                    <p class="text-slate-600 max-w-md text-sm sm:text-base">
                        Dirancang untuk membantu pengelola hasil panen dan logistik mengambil keputusan yang lebih jelas, terukur, dan transparan.
                    </p>
                </div>

                {{-- Bento Grid Layout --}}
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    {{-- Benefit 1 (Span 3) --}}
                    <div class="md:col-span-3 p-8 rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-50/70 border border-emerald-200/70 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mb-5 shadow-md shadow-emerald-600/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22V12"/></svg>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900 mb-3">Pengelolaan Panen &amp; Pengiriman Lebih Teratur</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Seluruh catatan panen, asal komoditas, tujuan, dan riwayat analisis tersimpan terstruktur sehingga mudah ditelusuri kapan saja dibutuhkan.
                        </p>
                    </div>

                    {{-- Benefit 2 (Span 3) --}}
                    <div class="md:col-span-3 p-8 rounded-3xl bg-gradient-to-br from-blue-50 to-indigo-50/70 border border-blue-200/70 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center mb-5 shadow-md shadow-blue-600/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900 mb-3">Keputusan Pengiriman Lebih Terukur</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Tidak perlu lagi menebak-nebak apakah produk kuat dikirim ke kota tujuan. Setiap keputusan didukung penilaian kondisi produk dan waktu tempuh.
                        </p>
                    </div>

                    {{-- Benefit 3 (Span 2) --}}
                    <div class="md:col-span-2 p-8 rounded-3xl bg-gradient-to-br from-amber-50 to-orange-50/70 border border-amber-200/70 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center mb-5 shadow-md shadow-amber-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-2">Deteksi Risiko Lebih Awal</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Kenali potensi penurunan kualitas lebih cepat agar tindakan pencegahan bisa dilakukan sebelum terlambat.
                        </p>
                    </div>

                    {{-- Benefit 4 (Span 4) --}}
                    <div class="md:col-span-4 p-8 rounded-3xl bg-gradient-to-br from-purple-50 to-fuchsia-50/60 border border-purple-200/70 hover:shadow-lg transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-600/20">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 3h5v5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 3H3v5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path stroke-linecap="round" stroke-linejoin="round" d="m15 9 6-6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-900 mb-2">Pemahaman Lebih Baik Terhadap Alternatif Rute &amp; Skenario</h3>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Bandingkan langsung rencana awal dengan berbagai skenario alternatif—seperti perubahan rute, jenis kendaraan, atau pengaturan suhu—sebelum Anda mengambil keputusan final.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =====================================================================
             SECTION E: FINAL CALL TO ACTION (Dark Navy + Vibrant Gradient Card)
        ===================================================================== --}}
        <section class="py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-6 lg:px-12">
                <div class="relative rounded-[2.5rem] bg-slate-900 overflow-hidden p-10 sm:p-14 lg:p-16 text-center shadow-2xl">
                    {{-- Decorative Gradients --}}
                    <div class="absolute -top-24 -left-24 w-72 h-72 bg-emerald-500/30 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-blue-500/30 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-2xl mx-auto">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-emerald-300 text-xs font-bold uppercase tracking-widest mb-6">
                            Mulai Sekarang
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-5">
                            Siap Merencanakan Pengiriman Hasil Panen Lebih Baik?
                        </h2>
                        <p class="text-slate-300 text-base sm:text-lg mb-10 leading-relaxed">
                            Coba AgriFlow AI untuk mencatat panen, memantau kondisi produk, dan mengevaluasi rute pengiriman dalam satu platform.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-slate-900 bg-emerald-400 rounded-2xl hover:bg-emerald-300 transition-all duration-300 shadow-lg shadow-emerald-500/25 hover:-translate-y-1">
                                    Daftar Sekarang
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            @endif

                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-white/10 border border-white/20 rounded-2xl hover:bg-white/20 transition-all duration-300 hover:-translate-y-1">
                                    Masuk ke Akun
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =====================================================================
             FOOTER
        ===================================================================== --}}
        <footer class="bg-slate-950 text-white py-12 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-2 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                    </div>
                    <span class="text-xl font-black tracking-tight">AgriFlow<span class="text-emerald-400">AI</span></span>
                </div>

                <div class="text-slate-400 text-sm text-center">
                    &copy; {{ date('Y') }} AgriFlow AI. Hak cipta dilindungi.
                </div>

                <div class="flex gap-6 text-sm font-medium text-slate-400">
                    <a href="#cara-kerja" class="hover:text-emerald-400 transition-colors">Cara Kerja</a>
                    <a href="#" class="hover:text-emerald-400 transition-colors">Privasi</a>
                    <a href="#" class="hover:text-emerald-400 transition-colors">Kontak</a>
                </div>
            </div>
        </footer>

    </div>
</body>
</html>