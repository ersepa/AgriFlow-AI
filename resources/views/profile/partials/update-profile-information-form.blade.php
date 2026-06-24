<section class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
    <header class="mb-8 border-b border-slate-100 pb-6">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Profile Information</h2>
        <p class="mt-2 text-slate-500 font-medium">Update your account's profile information and email address.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Name</label>
                <input name="name" type="text" value="{{ old('name', $user->name) }}" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-2xl font-bold transition-all shadow-lg shadow-slate-200">
                Save Profile
            </button>
            
            @if (session('status') === 'profile-updated')
                <span class="text-emerald-600 font-bold text-sm">Saved!</span>
            @endif
        </div>
    </form>
</section>