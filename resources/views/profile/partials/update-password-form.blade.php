<section class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
    <header class="mb-8 border-b border-slate-100 pb-6">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Security</h2>
        <p class="mt-2 text-slate-500 font-medium">Keep your account safe with a strong, unique password.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Current Password</label>
                <input name="current_password" type="password" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">New Password</label>
                    <input name="password" type="password" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Confirm New</label>
                    <input name="password_confirmation" type="password" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                </div>
            </div>
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-bold transition-all shadow-lg shadow-indigo-100">
            Update Password
        </button>
    </form>
</section>