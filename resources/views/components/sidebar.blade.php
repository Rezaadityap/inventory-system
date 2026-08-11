@php
    $sidebarMenus = [
        [
            'label' => 'Dashboard',
            'icon' => 'layout-grid',
            'route' => 'dashboard',
        ],
        [
            'label' => 'Kategori',
            'icon' => 'flask-conical',
            'route' => 'categories.index',
        ],
        [
            'label' => 'Customers',
            'icon' => 'contact',
            'route' => 'customers.index',
        ],
        [
            'label' => 'Users',
            'icon' => 'users',
            'route' => 'user.index',
        ],
        [
            'label' => 'Hak Akses',
            'icon' => 'lock',
            'route' => 'roles.index',
        ],
    ];
@endphp

<aside x-data="{ collapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('collapsed', value => localStorage.setItem('sidebarCollapsed', value))"
    :class="{
        'translate-x-0': mobileSidebarOpen,
        '-translate-x-full': !mobileSidebarOpen,
        'md:w-20': collapsed,
        'md:w-70': !collapsed,
    }"
    class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full p-5 bg-linear-to-br from-primary to-secondary rounded-r-[28px] shadow-lg shadow-black/5 transition-all duration-300 md:static md:inset-auto md:z-auto md:w-70 md:shrink-0 md:translate-x-0 md:rounded-[28px]">
    <script>
        (function(el) {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                el.classList.remove('md:w-70');
                el.classList.add('md:w-20');
            }
        })(document.currentScript.parentElement);
    </script>

    <button @click="mobileSidebarOpen = false"
        class="flex md:hidden items-center justify-center absolute right-5 top-5 w-8 h-8 rounded-full bg-white/10 text-white hover:bg-white/20 transition cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>

    <button @click="collapsed = !collapsed"
        class="hidden md:flex items-center justify-center absolute -right-3 top-8 w-6 h-6 rounded-full bg-primary-dark text-white shadow-md shadow-black/10 hover:scale-105 transition-transform cursor-pointer">
        <i data-lucide="chevron-left" class="w-3.5 h-3.5 transition-transform duration-300"
            :class="collapsed && 'rotate-180'"></i>
    </button>

    <div class="h-full overflow-y-auto">
        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-black/5 pr-10 md:pr-0">
            <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                class="w-11 h-11 rounded-2xl object-cover border border-black/5 shrink-0" alt="Foto User">
            <div class="flex flex-col overflow-hidden" x-show="!collapsed || mobileSidebarOpen" x-transition x-cloak>
                <small
                    class="text-[10.5px] uppercase tracking-wide text-white whitespace-nowrap">{{ Auth::user()->role->name }}</small>
                <p class="font-display text-sm font-semibold text-white whitespace-nowrap">{{ Auth::user()->name }}</p>
            </div>
        </div>

        <p class="text-[10.5px] uppercase tracking-wide text-white mb-3" x-show="!collapsed || mobileSidebarOpen"
            x-transition x-cloak>Main</p>

        <nav>
            <ul class="space-y-1">
                @foreach ($sidebarMenus as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            :class="collapsed && !mobileSidebarOpen && 'justify-center'"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-sm
                            {{ request()->routeIs($item['route']) ? 'bg-white text-primary-dark shadow-md shadow-primary/30' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-4.5 h-4.5 shrink-0"></i>
                            <span x-show="!collapsed || mobileSidebarOpen" x-transition x-cloak
                                class="whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>

<script>
    document.addEventListener('alpine:init', () => {
        document.addEventListener('alpine:updated', () => {
            if (window.lucide) lucide.createIcons();
        });
    });
</script>
