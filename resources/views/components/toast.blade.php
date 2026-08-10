@if (session('success'))
    <div class="toast-container" x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)">
        <div x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-6 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-6 scale-95"
            x-cloak class="toast toast-success">
            <i data-lucide="circle-check" class="toast-icon"></i>
            <div>
                <p class="toast-title">Berhasil</p>
                <p class="toast-message">{{ session('success') }}</p>
            </div>
            <span class="toast-close" @click="show = false">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </span>
        </div>
    </div>
@endif
