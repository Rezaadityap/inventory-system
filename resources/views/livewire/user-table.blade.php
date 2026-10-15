<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        abort_if($id === Auth::id(), 403);

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        session()->flash('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function with()
    {
        return [
            'users' => User::query()
                ->with('role')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('username', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex items-center flex-col md:flex-row gap-3 justify-between">
        <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full md:w-auto"
            placeholder="Cari...">
        <button class="btn btn-primary w-full md:w-auto" type="button" @click="createModal = true">Tambah</button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aktivasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="table-row" wire:key="user-{{ $user->id }}">
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->role)
                                <span class="table-badge table-badge-success">{{ $user->role->name }}</span>
                            @else
                                <span class="table-badge table-badge-warning">Belum ada role</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->is_active)
                                <span class="table-badge table-badge-success">Aktif</span>
                            @else
                                <span class="table-badge table-badge-warning">Menunggu</span>
                            @endif
                        </td>
                        <td>
                            <label
                                class="relative inline-flex items-center {{ $user->id === auth()->id() ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                <input type="checkbox" class="sr-only peer" @checked($user->is_active)
                                    @disabled($user->id === auth()->id()) wire:click="toggleActive({{ $user->id }})">
                                <div
                                    class="w-9 h-5 rounded-full bg-gray-200 transition-colors after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-success peer-checked:after:translate-x-4 peer-focus:ring-2 peer-focus:ring-primary/40 peer-disabled:opacity-50">
                                </div>
                            </label>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="editModal = true; editData = {
                                        id: {{ $user->id }},
                                        name: @js($user->name),
                                        username: @js($user->username),
                                        email: @js($user->email),
                                        role: @js($user->role?->name ?? ''),
                                    }">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $user->id }},
                                        name: @js($user->name)
                                    }">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} data</span>
        <nav class="pagination-nav">
            {{ $users->links('pagination.custom') }}
        </nav>
    </div>
</div>
