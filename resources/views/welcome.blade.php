<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriFlow AI - Smart Agriculture Intelligence</title>

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

            /* Menghubungkan step satu ke yang lain */
    .step-connector::after {
        content: '';
        position: absolute;
        top: 32px;
        left: 60%;
        width: 100%;
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    .group:last-child .step-connector::after { display: none; }
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 overflow-x-hidden selection:bg-emerald-500 selection:text-white">

    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden bg-grid-pattern opacity-40"></div>
    <div class="fixed top-[-10%] left-[-10%] w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-50 z-0 animate-pulse"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-indigo-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 z-0 animate-pulse" style="animation-duration: 4s;"></div>

    <div class="relative z-10 min-h-screen flex flex-col">
        
        <header class="py-6 px-6 lg:px-12 flex justify-between items-center animate-fade-in-up">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-2.5 rounded-xl shadow-lg shadow-emerald-200/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
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
                        <a href="{{ route('login') }}" class="hidden sm:block font-bold text-slate-600 hover:text-emerald-600 transition-colors">Log in</a>
                    <!-- awal register -->
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="relative inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white transition-all duration-300 bg-slate-900 rounded-full hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </header>

        <main class="flex-1 flex flex-col lg:flex-row items-center justify-center px-6 lg:px-12 py-12 gap-16 max-w-7xl mx-auto w-full">
            
            <div class="flex-1 text-center lg:text-left z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold mb-6 animate-fade-in-up">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    System v2.0 is Live
                </div>

                <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight animate-fade-in-up delay-100">
                    Farming Meets <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-indigo-600">
                        Artificial Intelligence
                    </span>
                </h1>

                <p class="text-lg text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed animate-fade-in-up delay-200 font-medium">
                    Optimize your harvest, predict risks with pinpoint accuracy, and manage shipments seamlessly using our real-time AI analytics engine.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start animate-fade-in-up delay-300">
                    <!-- awal register -->
                    <a href="{{ route('login') }}" class="w-full sm:w-auto relative group inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-300 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl hover:shadow-xl hover:shadow-emerald-500/30 hover:-translate-y-1 overflow-hidden">
                        <div class="absolute inset-0 w-full h-full -x-10 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-150%] group-hover:translate-x-[150%] transition-transform duration-700 ease-in-out"></div>
                        Create Account
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-2xl hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 transition-all duration-300 hover:-translate-y-1">
                        Sign In
                    </a>
                </div>
            </div>

            <div class="flex-1 relative w-full max-w-lg lg:max-w-none animate-fade-in-up delay-200 hidden md:block">
                <div class="relative bg-white/80 backdrop-blur-2xl border border-slate-100 p-6 rounded-3xl shadow-2xl animate-float z-20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm font-bold text-slate-500">AI Risk Analysis</p>
                            <p class="text-2xl font-black text-slate-800">Stable</p>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-2xl">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 w-3/4 rounded-full"></div>
                        </div>
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 w-1/2 rounded-full"></div>
                        </div>
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500 w-1/4 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-8 -left-8 bg-white border border-slate-100 p-5 rounded-3xl shadow-xl animate-float-delayed z-30">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-100 rounded-2xl">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Total Harvest</p>
                            <p class="text-xl font-black text-slate-800">2,450 kg</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -top-10 -right-8 bg-gradient-to-br from-indigo-500 to-blue-600 p-5 rounded-3xl shadow-xl shadow-indigo-500/20 animate-float z-10">
                    <p class="text-xs font-bold text-indigo-100 uppercase mb-1">Insight Score</p>
                    <p class="text-3xl font-black text-white">98.5</p>
                </div>
                
                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-100 to-indigo-50 rounded-[3rem] transform rotate-3 scale-105 -z-10 transition-transform hover:rotate-6 duration-500"></div>
            </div>
            
        </main>
        <section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-black text-slate-900 mb-4">Solusi End-to-End untuk Pertanian Modern</h2>
            <p class="text-slate-600 max-w-2xl mx-auto">Kami membantu rantai pasok pertanian Indonesia menjadi lebih transparan, efisien, dan minim risiko.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-emerald-200 transition-all group">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-6">📦</div>
                <h3 class="text-xl font-bold mb-3">Manajemen Panen</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Catat setiap siklus panen dengan presisi. Integrasikan data berat, kualitas, dan varietas secara digital.</p>
            </div>
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition-all group">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mb-6">📊</div>
                <h3 class="text-xl font-bold mb-3">Analisis Kualitas</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Dapatkan insight berbasis AI tentang potensi pasar dan tingkat kesegaran komoditas Anda sebelum didistribusikan.</p>
            </div>
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-cyan-200 transition-all group">
                <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center mb-6">🚚</div>
                <h3 class="text-xl font-bold mb-3">Logistik Real-Time</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Pantau rute pengantaran dan prediksi risiko kerusakan selama transit menggunakan sistem AI monitoring kami.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col lg:flex-row gap-12 items-center">
        <div class="flex-1">
            <span class="text-emerald-400 font-bold tracking-widest uppercase text-xs">Konteks Indonesia</span>
            <h2 class="text-4xl font-black mt-3 mb-6">Mengatasi Masalah Klasik Rantai Pasok Nasional</h2>
            <ul class="space-y-4">
                <li class="flex gap-4">
                    <span class="text-emerald-400">✔</span>
                    <p class="text-slate-300"><strong class="text-white">Waste Tinggi:</strong> Tingginya angka kehilangan hasil panen (post-harvest loss) akibat buruknya manajemen logistik di Indonesia.</p>
                </li>
                <li class="flex gap-4">
                    <span class="text-emerald-400">✔</span>
                    <p class="text-slate-300"><strong class="text-white">Ketimpangan Harga:</strong> Data yang tidak transparan menyebabkan harga di tingkat petani jauh lebih rendah dibanding pasar kota.</p>
                </li>
                <li class="flex gap-4">
                    <span class="text-emerald-400">✔</span>
                    <p class="text-slate-300"><strong class="text-white">Kurangnya Prediksi:</strong> Petani sering kesulitan memprediksi risiko cuaca dan jalur distribusi yang optimal.</p>
                </li>
            </ul>
        </div>
        <div class="flex-1 w-full bg-slate-800 p-8 rounded-3xl border border-slate-700">
            <h4 class="font-bold mb-4">Visi Kami</h4>
            <p class="text-slate-400 italic">"Mendigitalkan pertanian Indonesia dengan teknologi AI untuk memastikan komoditas sampai ke tangan konsumen dengan kualitas terbaik, efisiensi maksimal, dan keuntungan adil bagi petani."</p>
        </div>
    </div>
</section>
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">

        <div class="text-center mb-16">
            <span class="text-emerald-600 font-bold uppercase tracking-widest text-xs">
                Tantangan yang Kita Hadapi
            </span>

            <h2 class="text-4xl font-black text-slate-900 mt-4 mb-4">
                Permasalahan Rantai Pasok Pertanian di Indonesia
            </h2>

            <p class="max-w-3xl mx-auto text-slate-600 text-lg">
                Setiap hari, hasil pertanian bernilai tinggi mengalami kerusakan
                akibat distribusi yang tidak efisien, kurangnya pemantauan,
                dan minimnya pengambilan keputusan berbasis data.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">

            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8 hover:-translate-y-2 hover:shadow-xl transition-all duration-300">
                <div class="text-5xl mb-4">📉</div>

                <h3 class="text-4xl font-black text-rose-600 mb-2">
                    23–48%
                </h3>

                <p class="font-bold text-slate-900 mb-3">
                    Kehilangan Pasca Panen
                </p>

                <p class="text-slate-600 text-sm leading-relaxed">
                    Sebagian besar komoditas hortikultura mengalami kerusakan
                    selama penyimpanan, penanganan, dan distribusi sebelum
                    sampai ke konsumen.
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8 hover:-translate-y-2 hover:shadow-xl transition-all duration-300">
                <div class="text-5xl mb-4">🚚</div>

                <h3 class="text-4xl font-black text-indigo-600 mb-2">
                    Risiko Logistik
                </h3>

                <p class="font-bold text-slate-900 mb-3">
                    Distribusi Kurang Efisien
                </p>

                <p class="text-slate-600 text-sm leading-relaxed">
                    Keterlambatan pengiriman, pemilihan rute yang kurang optimal,
                    dan minimnya pemantauan meningkatkan risiko kerusakan produk.
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8 hover:-translate-y-2 hover:shadow-xl transition-all duration-300">
                <div class="text-5xl mb-4">🌱</div>

                <h3 class="text-4xl font-black text-emerald-600 mb-2">
                    Dampak Lingkungan
                </h3>

                <p class="font-bold text-slate-900 mb-3">
                    Emisi Karbon Meningkat
                </p>

                <p class="text-slate-600 text-sm leading-relaxed">
                    Pemborosan pangan dan distribusi yang tidak efisien
                    berkontribusi terhadap meningkatnya emisi karbon
                    dalam rantai pasok pertanian.
                </p>
            </div>

        </div>

        <div class="mt-12 bg-gradient-to-r from-emerald-600 to-indigo-600 text-white rounded-3xl p-8 text-center">
            <h3 class="text-2xl font-black mb-3">
                AgriFlow AI Hadir Sebagai Solusi
            </h3>

            <p class="max-w-3xl mx-auto text-emerald-50">
                Dengan prediksi risiko berbasis AI, optimasi rute pengiriman,
                analisis keberlanjutan, dan pemantauan distribusi secara real-time,
                AgriFlow AI membantu mengurangi food waste dan meningkatkan
                efisiensi rantai pasok pertanian Indonesia.
            </p>
        </div>

    </div>
</section>
<section class="py-24 bg-slate-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="text-center mb-20">
            <h2 class="text-4xl font-black text-slate-900 mb-4">Cara Kerja AgriFlow AI</h2>
            <p class="text-slate-600 font-medium">Transformasi alur logistik Anda dalam empat langkah cerdas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Data Ingestion -->
            <div class="relative group cursor-default">
                <div class="step-connector relative z-10 flex flex-col items-center text-center transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-white border-4 border-emerald-500 rounded-3xl flex items-center justify-center text-emerald-600 text-3xl font-black mb-8 shadow-xl shadow-emerald-500/10 group-hover:bg-emerald-500 group-hover:text-white transition-colors">01</div>
                    <h3 class="font-bold text-lg text-slate-900 mb-3">Data Ingestion</h3>
                    <p class="text-slate-500 text-sm leading-relaxed px-2">Input data panen secara real-time ke sistem terpusat kami.</p>
                </div>
            </div>

            <!-- AI Processing -->
            <div class="relative group cursor-default">
                <div class="step-connector relative z-10 flex flex-col items-center text-center transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-white border-4 border-indigo-500 rounded-3xl flex items-center justify-center text-indigo-600 text-3xl font-black mb-8 shadow-xl shadow-indigo-500/10 group-hover:bg-indigo-500 group-hover:text-white transition-colors">02</div>
                    <h3 class="font-bold text-lg text-slate-900 mb-3">AI Processing</h3>
                    <p class="text-slate-500 text-sm leading-relaxed px-2">AI menganalisis risiko kualitas dan potensi kerusakan komoditas.</p>
                </div>
            </div>

            <!-- Route Optimization -->
            <div class="relative group cursor-default">
                <div class="step-connector relative z-10 flex flex-col items-center text-center transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-white border-4 border-cyan-500 rounded-3xl flex items-center justify-center text-cyan-600 text-3xl font-black mb-8 shadow-xl shadow-cyan-500/10 group-hover:bg-cyan-500 group-hover:text-white transition-colors">03</div>
                    <h3 class="font-bold text-lg text-slate-900 mb-3">Route Optimization</h3>
                    <p class="text-slate-500 text-sm leading-relaxed px-2">Menentukan rute logistik tercepat untuk meminimalisir waktu transit.</p>
                </div>
            </div>

            <!-- Smart Delivery -->
            <div class="relative group cursor-default">
                <div class="relative z-10 flex flex-col items-center text-center transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-white border-4 border-rose-500 rounded-3xl flex items-center justify-center text-rose-600 text-3xl font-black mb-8 shadow-xl shadow-rose-500/10 group-hover:bg-rose-500 group-hover:text-white transition-colors">04</div>
                    <h3 class="font-bold text-lg text-slate-900 mb-3">Smart Delivery</h3>
                    <p class="text-slate-500 text-sm leading-relaxed px-2">Monitoring penuh sampai produk tiba ke tangan konsumen dengan aman.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl lg:rounded-[3rem] p-6 sm:p-8 lg:p-12 text-white overflow-hidden relative">

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">

                <div>
                    <h2 class="text-2xl sm:text-3xl font-black mb-6">
                        Masa Depan Pangan yang Transparan
                    </h2>

                    <div class="space-y-6">

                        <div>
                            <h4 class="text-emerald-400 font-bold mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg>
                                Traceability Penuh
                            </h4>

                            <p class="text-slate-400 text-sm leading-relaxed">
                                Setiap komoditas memiliki rekam jejak digital. Konsumen tahu persis dari mana bahan pangan mereka berasal, meningkatkan kepercayaan pasar.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-indigo-400 font-bold mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.883M8 12h8M8 8h8">
                                    </path>
                                </svg>
                                Mitigasi Food Waste
                            </h4>

                            <p class="text-slate-400 text-sm leading-relaxed">
                                Dengan AI, kami memprediksi masa simpan produk dan mengoptimalkan rantai pasok untuk mencegah pembuangan hasil panen yang tidak perlu.
                            </p>
                        </div>

                    </div>
                </div>

                <div class="bg-white/5 p-5 sm:p-6 lg:p-8 rounded-2xl border border-white/10 backdrop-blur-sm">

                    <p class="text-base sm:text-lg lg:text-xl text-emerald-400 font-black italic mb-4">
                        "Teknologi bukan sekadar tentang angka, tapi tentang memastikan setiap butir hasil bumi Indonesia memberikan nilai ekonomi dan manfaat bagi banyak orang."
                    </p>

                    <p class="text-xs sm:text-sm font-bold tracking-widest text-slate-400 uppercase">
                        — AgriFlow Vision Team
                    </p>

                </div>

            </div>

            <div class="absolute -bottom-24 -right-24 w-48 h-48 lg:w-64 lg:h-64 bg-emerald-500/20 rounded-full blur-3xl"></div>

        </div>
    </div>
</section>
    </div>
    <section class="py-20">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-black text-slate-900 mb-6">Siap Memulai Transformasi Pertanian Anda?</h2>
        <p class="text-lg text-slate-600 mb-10">Bergabunglah dengan ratusan petani modern yang telah mengoptimalkan hasil panen mereka bersama AgriFlow AI.</p>
        <!-- awal  register -->
        <a href="{{ route('login') }}" class="inline-flex items-center px-10 py-5 text-lg font-bold text-white bg-slate-900 rounded-2xl hover:bg-emerald-600 transition-all duration-300 shadow-xl hover:shadow-emerald-500/30 hover:-translate-y-1">
            Daftar Sekarang Secara Gratis
        </a>
    </div>
</section>

<footer class="bg-slate-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
        <div class="flex items-center gap-3">
            <span class="text-xl font-black tracking-tight">AgriFlow<span class="text-emerald-400">AI</span></span>
        </div>
        <div class="text-slate-500 text-sm">
            &copy; {{ date('Y') }} AgriFlow AI. All rights reserved.
        </div>
        <div class="flex gap-6 text-sm text-slate-400">
            <a href="#" class="hover:text-emerald-400">Tentang</a>
            <a href="#" class="hover:text-emerald-400">Privasi</a>
            <a href="#" class="hover:text-emerald-400">Kontak</a>
        </div>
    </div>
</footer>
</body>
</html>