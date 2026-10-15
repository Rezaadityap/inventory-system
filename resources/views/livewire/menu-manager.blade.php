@section('page-title')
    Kelola Menu
@endsection
@section('page-subtitle')
    Kelola struktur, urutan, dan akses menu aplikasi
@endsection

<div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{
    showModal: false,
    isEdit: false,
    form: { id: null, name: '', code: '', route: '', icon: '', parent_id: '', order: 0, is_active: true },
    showDeleteModal: false,
    deleteData: {},
    openCreate() {
        this.isEdit = false;
        this.form = { id: null, name: '', code: '', route: '', icon: '', parent_id: '', order: 0, is_active: true };
        this.showModal = true;
    },
    openEdit(menu) {
        this.isEdit = true;
        this.form = { ...menu };
        this.showModal = true;
    },
    submit() {
        $wire.save(this.form.id, this.form.name, this.form.code, this.form.route, this.form.icon, this.form.parent_id, this.form.order, this.form.is_active);
    },
}" x-on:menu-saved.window="showModal = false"
    x-on:menu-deleted.window="showDeleteModal = false">
    @include('components.toast')

    <div class="flex items-center flex-col md:flex-row gap-3 justify-between mb-3">
        <h2 class="font-display font-semibold text-text-primary">Daftar Menu</h2>
        <button type="button" class="btn btn-primary w-full md:w-auto" @click="openCreate">
            Tambah Menu
        </button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Code</th>
                    <th>Route</th>
                    <th>Urutan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr class="table-row" wire:key="menu-{{ $menu->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $menu->name }}</td>
                        <td>{{ $menu->code }}</td>
                        <td>{{ $menu->route ?? '-' }}</td>
                        <td>{{ $menu->order }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="openEdit({
                                        id: {{ $menu->id }},
                                        name: @js($menu->name),
                                        code: @js($menu->code),
                                        route: @js($menu->route),
                                        icon: @js($menu->icon),
                                        parent_id: {{ $menu->parent_id ?? 'null' }},
                                        order: {{ $menu->order }},
                                        is_active: {{ $menu->is_active ? 'true' : 'false' }},
                                    })">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteData = { id: {{ $menu->id }}, name: @js($menu->name) }; showDeleteModal = true">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @foreach ($menu->children as $child)
                        <tr class="table-row" wire:key="menu-{{ $child->id }}">
                            <td></td>
                            <td class="pl-8 text-text-secondary">↳ {{ $child->name }}</td>
                            <td>{{ $child->code }}</td>
                            <td>{{ $child->route ?? '-' }}</td>
                            <td>{{ $child->order }}</td>
                            <td>
                                <div class="flex space-x-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                        @click="openEdit({
                                            id: {{ $child->id }},
                                            name: @js($child->name),
                                            code: @js($child->code),
                                            route: @js($child->route),
                                            icon: @js($child->icon),
                                            parent_id: {{ $child->parent_id ?? 'null' }},
                                            order: {{ $child->order }},
                                            is_active: {{ $child->is_active ? 'true' : 'false' }},
                                        })">
                                        <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                        @click="deleteData = { id: {{ $child->id }}, name: @js($child->name) }; showDeleteModal = true">
                                        <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit Modal --}}
    <div x-show="showModal" x-cloak x-transition.opacity @keydown.escape.window="showModal = false"
        class="modal-overlay">
        <div x-show="showModal" x-transition @click.outside="showModal = false" class="modal-panel">
            <form @submit.prevent="submit">
                <div class="modal-header">
                    <h2 x-text="isEdit ? 'Edit Menu' : 'Tambah Menu'"></h2>
                    <span class="modal-close" @click="showModal = false">&times;</span>
                </div>

                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label required">Nama Menu</label>
                        <input type="text" x-model="form.name" class="form-input" placeholder="Masukan Nama Menu">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label class="form-label required">Code</label>
                        <input type="text" x-model="form.code" class="form-input" placeholder="Masukan Code Menu">
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>

                    <div>
                        <label class="form-label">Route</label>
                        <input type="text" x-model="form.route" class="form-input" placeholder="Masukan Nama Route">
                        <x-input-error :messages="$errors->get('route')" class="mt-1" />
                    </div>

                    <div>
                        <label class="form-label">Icon</label>
                        <input type="text" x-model="form.icon" class="form-input"
                            placeholder="Nama icon lucide, mis. layout-grid">
                        <x-input-error :messages="$errors->get('icon')" class="mt-1" />
                        <p class="text-xs text-text-secondary mt-1">
                            Lihat nama icon di
                            <a href="https://lucide.dev/icons" target="_blank"
                                class="text-primary hover:underline">lucide.dev/icons</a>.
                            Kosongkan untuk pakai icon default.
                        </p>
                    </div>

                    <div>
                        <label class="form-label">Parent Menu</label>
                        <select x-model="form.parent_id" class="form-input">
                            <option value="">Tidak ada (Menu Utama)</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" x-show="form.id !== {{ $parent->id }}">
                                    {{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-1" />
                    </div>

                    <div>
                        <label class="form-label">Urutan</label>
                        <input type="number" x-model="form.order" class="form-input" placeholder="0">
                        <x-input-error :messages="$errors->get('order')" class="mt-1" />
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="w-4 h-4 rounded accent-primary">
                        <span class="text-sm text-text-primary">Tampilkan di sidebar</span>
                    </label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" @click="showModal = false">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation --}}
    <div x-show="showDeleteModal" x-cloak x-transition.opacity @keydown.escape.window="showDeleteModal = false"
        class="modal-overlay">
        <div x-show="showDeleteModal" x-transition @click.outside="showDeleteModal = false" class="modal-panel">
            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-danger">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Hapus Menu?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau hapus <span class="font-medium text-text-primary"
                            x-text="deleteData.name"></span>?
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="showDeleteModal = false">Batal</button>
                <button type="button" class="btn bg-danger text-white hover:bg-danger/90"
                    @click="$wire.delete(deleteData.id)">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
