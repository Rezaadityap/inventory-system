@section('page-title')
    Kelola Role
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

<div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{
    showModal: false,
    isEdit: false,
    form: { id: null, name: '' },
    showDeleteModal: false,
    deleteData: {},
    openCreate() {
        this.isEdit = false;
        this.form = { id: null, name: '' };
        this.showModal = true;
    },
    openEdit(role) {
        this.isEdit = true;
        this.form = { ...role };
        this.showModal = true;
    },
    submit() {
        $wire.save(this.form.id, this.form.name);
    },
}" x-on:role-saved.window="showModal = false"
    x-on:role-deleted.window="showDeleteModal = false">
    @include('components.toast')

    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-semibold text-text-primary">Daftar Role</h2>
        <button type="button" class="btn btn-primary" @click="openCreate">
            Tambah Role
        </button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nama Role</th>
                    <th>Jumlah Permission</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr class="table-row" wire:key="role-{{ $role->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                            <span class="table-badge table-badge-success">{{ $role->permissions_count }}
                                Permission</span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="{{ route('roles.permissions', $role) }}"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer">
                                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                                    Permission
                                </a>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="openEdit({ id: {{ $role->id }}, name: @js($role->name) })">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteData = { id: {{ $role->id }}, name: @js($role->name) }; showDeleteModal = true">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-text-secondary">
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
                    <h2 x-text="isEdit ? 'Edit Role' : 'Tambah Role'"></h2>
                    <span class="modal-close" @click="showModal = false">&times;</span>
                </div>

                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label required">Nama Role</label>
                        <input type="text" x-model="form.name" class="form-input" placeholder="Masukan Nama Role">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
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
                    <p class="font-display font-semibold text-text-primary">Hapus Role?</p>
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
