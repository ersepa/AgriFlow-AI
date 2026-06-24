<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - AgriFlow AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; transform: translateY(10px); }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-white min-h-screen flex selection:bg-emerald-500 selection:text-white">

    <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-32 relative z-10">
        
        <a href="/" class="absolute top-8 left-8 sm:left-16 flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-emerald-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Home
        </a>

        <div class="max-w-sm w-full mx-auto mt-12 lg:mt-0 animate-fade-in">
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Welcome back</h2>
                <p class="text-slate-500 mt-2 font-medium">Please enter your details to sign in.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-sm text-rose-600 font-medium">
                    Oops! Something went wrong. Please check your credentials.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="animate-fade-in delay-100">
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                </div>

                <div class="animate-fade-in delay-200">
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-slate-900 outline-none placeholder:text-slate-400 font-medium shadow-sm">
                </div>

                <div class="flex items-center justify-between animate-fade-in delay-200">
                    <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded-md border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 cursor-pointer">
                        <span class="ml-2 text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-500 transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <div class="pt-2 animate-fade-in delay-200">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-500/30 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transform hover:-translate-y-0.5 transition-all duration-200">
                        Sign In
                    </button>
                </div>
            </form>

            <p class="mt-8 text-center text-sm font-medium text-slate-500 animate-fade-in delay-200">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:text-emerald-500 transition-colors">Sign up</a>
            </p>
        </div>
    </div>

    <div class="hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden items-center justify-center">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/40 to-indigo-900/40 mix-blend-overlay"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
        
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="relative z-10 max-w-md p-8 bg-white/10 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <span class="text-xl font-black text-white tracking-tight">AgriFlow AI</span>
            </div>
            <h3 class="text-2xl font-bold text-white mb-4 leading-snug">"AI has revolutionized how we manage our harvest risks and distribution."</h3>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center border border-white/20 text-white font-bold text-sm">A</div>
                <div>
                    <p class="text-sm font-bold text-white">Admin Team</p>
                    <p class="text-xs font-medium text-slate-400">System Operator</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>