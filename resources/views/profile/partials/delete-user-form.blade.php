<section class="bg-red-50/30 p-8 rounded-3xl border border-red-100 shadow-sm">
    <header class="mb-6">
        <h2 class="text-2xl font-black text-red-600 tracking-tight">Delete Account</h2>
        <p class="mt-2 text-red-500/80 font-medium">Once your account is deleted, all of your resources and data will be permanently deleted.</p>
    </header>

    <button 
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-2xl font-bold transition-all shadow-lg shadow-red-200">
        Delete Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
            @csrf
            @method('delete')

            <h2 class="text-2xl font-black text-slate-900">Are you sure?</h2>
            <p class="mt-2 text-slate-500">This action cannot be undone. Please enter your password to confirm.</p>

            <div class="mt-6">
                <input name="password" type="password" 
                    class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-red-500" 
                    placeholder="Enter your password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <x-secondary-button x-on:click="$dispatch('close')" class="!rounded-xl px-6 py-3">
                    Cancel
                </x-secondary-button>
                
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-bold transition-all">
                    Permanently Delete
                </button>
            </div>
        </form>
    </x-modal>
</section>