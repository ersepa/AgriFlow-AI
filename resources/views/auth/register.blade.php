<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - AgriFlow AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; transform: translateY(10px); }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-white min-h-screen flex selection:bg-indigo-500 selection:text-white">

    <a href="{{ url('/') }}" class="absolute top-8 left-8 z-50 flex items-center gap-2 text-slate-400 hover:text-indigo-600 transition-colors font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Home
    </a>

    <div class="hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden items-center justify-center">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 to-slate-900/80 mix-blend-overlay"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen filter blur-[120px] opacity-30"></div>
        
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="relative z-10 max-w-md p-8 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl">
            <h3 class="text-3xl font-black text-white mb-2">Join the Future</h3>
            <p class="text-indigo-200 mb-6 font-medium leading-relaxed">Start tracking your agriculture shipments with the power of Artificial Intelligence today.</p>
            
            <ul class="space-y-4">
                <li class="flex items-center gap-3 text-sm font-medium text-white">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Real-time risk distribution analysis
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Automated smart recommendations
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Comprehensive harvest tracking
                </li>
            </ul>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-32 relative z-10 bg-slate-50 lg:bg-white">
        
        <div class="max-w-sm w-full mx-auto mt-12 lg:mt-0 animate-fade-in">
            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Create Account</h2>
                <p class="text-slate-500 mt-2 font-medium">Get your free AgriFlow AI account now.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-sm text-rose-600 font-medium">
                    Oops! Please check the fields below.
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div class="animate-fade-in delay-100">
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-1.5">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="animate-fade-in delay-100">
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="name@company.com" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="animate-fade-in delay-200">
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required placeholder="Create a strong password" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="animate-fade-in delay-200">
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-1.5">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repeat your password" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="pt-4 animate-fade-in delay-300">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:-translate-y-0.5 transition-all duration-200">
                        Create Account
                    </button>
                </div>
            </form>

            <p class="mt-8 text-center text-sm font-medium text-slate-500 animate-fade-in delay-300">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-500 transition-colors">Sign in</a>
            </p>
        </div>
    </div>

</body>
</html>