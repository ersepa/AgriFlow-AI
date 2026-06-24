<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - AgriFlow AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; transform: translateY(10px); }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .bg-grid-pattern {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-emerald-500 selection:text-white">

    <!-- Background Dekorasi -->
    <div class="absolute inset-0 z-0 pointer-events-none bg-grid-pattern opacity-40"></div>
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 z-0 animate-pulse" style="animation-duration: 4s;"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-[128px] opacity-30 z-0 animate-pulse" style="animation-duration: 5s;"></div>

    <div class="w-full max-w-md px-6 relative z-10 animate-fade-in">
        <!-- Card Utama -->
        <div class="bg-white/80 backdrop-blur-xl border border-slate-100 rounded-3xl shadow-2xl p-8 sm:p-10">
            
            <!-- Icon & Judul -->
            <div class="flex flex-col items-center text-center mb-8">
                <div class="bg-emerald-50 p-4 rounded-2xl mb-5 text-emerald-600 border border-emerald-100 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Forgot Password?</h2>
                <p class="text-slate-500 mt-2 text-sm font-medium leading-relaxed">
                    No worries! Enter your email address and we'll send you a link to reset it.
                </p>
            </div>

            <!-- Pesan Sukses (Session Status) -->
            @if (session('status'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700 font-bold text-center animate-fade-in">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Pesan Error Validation -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-sm text-rose-600 font-medium animate-fade-in">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <!-- Input Email -->
                <div class="animate-fade-in delay-100">
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2 animate-fade-in delay-200">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-500/30 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transform hover:-translate-y-0.5 transition-all duration-200">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <!-- Tombol Kembali ke Login -->
            <div class="mt-8 text-center animate-fade-in delay-200">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-emerald-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>