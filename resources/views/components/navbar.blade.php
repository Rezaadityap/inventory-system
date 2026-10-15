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
        <div class="relative" x-data="{
            open: false,
            loading: false,
            unread: {{ Auth::user()->unreadNotifications()->count() }},
            notifications: [],
            load() {
                this.open = !this.open;
                if (!this.open) return;
                this.loading = true;
                fetch('{{ route('notifications.index') }}', { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(d => { this.notifications = d.notifications; this.unread = 0; this.loading = false; })
                    .catch(() => this.loading = false);
            },
        }" @click.outside="open = false">
            <button @click="load()"
                class="relative w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition cursor-pointer">
                <i data-lucide="bell" class="w-4.5 h-4.5 text-gray-600"></i>
                <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
                    class="absolute -top-1 -right-1 min-w-4.5 h-4.5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-semibold"></span>
            </button>

            <div x-show="open" x-transition x-cloak
                class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-2xl shadow-lg shadow-black/10 border border-black/5 z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 font-display font-semibold text-sm text-gray-800">
                    Notifikasi
                </div>
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                    <p x-show="loading" class="p-4 text-sm text-gray-400 text-center">Memuat...</p>
                    <template x-for="notification in notifications" :key="notification.id">
                        <a :href="notification.url || '#'" class="block px-4 py-3 hover:bg-gray-50 transition">
                            <p class="text-sm font-medium text-gray-800" x-text="notification.title"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="notification.message"></p>
                            <p class="text-[10.5px] text-gray-400 mt-1" x-text="notification.created_at"></p>
                        </a>
                    </template>
                    <p x-show="!loading && notifications.length === 0" class="p-4 text-sm text-gray-400 text-center">
                        Tidak ada notifikasi.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                class="w-10 h-10 rounded-full object-cover border border-black/5" alt="{{ Auth::user()->name }}">
            <div class="hidden md:flex flex-col">
                <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                <small class="text-[10.5px] text-gray-400">{{ Auth::user()->role?->name ?? 'Member' }}</small>
            </div>
        </div>
    </div>
</header>
