@section('page-title')
    Kelola Permission
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

<div class="w-full bg-white p-5 shadow-md rounded-xl">
    @include('components.toast')

    <div class="flex items-center justify-between mb-3">
        <div>
            <a href="{{ route('roles.index') }}"
                class="inline-flex items-center gap-1 text-sm text-text-secondary hover:text-primary mb-1">
                Kembali ke Role
            </a>
            <h2 class="font-display font-semibold text-text-primary">Permission untuk "{{ $role->name }}"</h2>
        </div>
    </div>

    <form wire:submit="save">
        <div class="table-wrapper" wire:loading.class="opacity-50">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th>Menu</th>
                        <th>Lihat</th>
                        <th>Tambah</th>
                        <th>Edit</th>
                        <th>Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menuGrouped as $group)
                        <tr class="table-row">
                            <td>{{ $group['menu_name'] }}</td>
                            @foreach ($group['permissions'] as $permission)
                                <td>
                                    <input type="checkbox" wire:model="selectedPermissions"
                                        value="{{ $permission['name'] }}"
                                        class="w-4 h-4 rounded border-secondary accent-primary cursor-pointer">
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-text-secondary">
                                Belum ada menu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" class="btn btn-primary">Simpan Permission</button>
        </div>
    </form>
</div>
