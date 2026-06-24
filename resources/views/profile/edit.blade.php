<x-app-layout>
    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Account Settings</h1>
            <p class="text-slate-500 mt-2 font-medium">Manage your personal profile and security preferences.</p>
        </div>

        <div class="space-y-8">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                @include('profile.partials.update-password-form')
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>