<header class="w-full flex items-center justify-between p-5 bg-white rounded-[28px] shadow-lg shadow-black/5 mb-5">
    <div class="flex items-center gap-3">
        <button @click="mobileSidebarOpen = true"
            class="flex md:hidden items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 transition shrink-0 cursor-pointer">
            <i data-lucide="menu" class="w-4.5 h-4.5 text-gray-600"></i>
        </button>
        <div>
            <p class="font-display text-lg font-semibold text-gray-800">@yield('page-title')</p>
            <small class="text-xs text-gray-400">@yield('page-subtitle')</small>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button
            class="relative w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition">
            <i data-lucide="bell" class="w-4.5 h-4.5 text-gray-600"></i>
            <span class="absolute top-2 right-2.5 w-2 h-2 rounded-full bg-red-500"></span>
        </button>

        <div class="flex items-center gap-3">
            <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                class="w-10 h-10 rounded-full object-cover border border-black/5" alt="{{ Auth::user()->name }}">
            <div class="hidden md:flex flex-col">
                <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                <small class="text-[10.5px] text-gray-400">{{ Auth::user()->getRoleNames()->first() ?? 'Member' }}</small>
            </div>
        </div>
    </div>
</header>
