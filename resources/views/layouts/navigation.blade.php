<div class="flex items-center">
    <x-dropdown align="right" width="48">
        
        <x-slot name="trigger">
            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-xl text-slate-600 bg-slate-50 hover:bg-slate-100 hover:text-slate-800 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                <div>{{ Auth::user()->name ?? 'Admin' }}</div>

                <div class="ml-2">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')" class="font-medium text-slate-600 hover:text-emerald-600">
                {{ __('Profile') }}
            </x-dropdown-link>

            <hr class="border-slate-100">

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();" 
                        class="font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </form>
        </x-slot>
        
    </x-dropdown>
</div>