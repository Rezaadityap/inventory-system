<?php

use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        return [
            'drivers' => Driver::query()
                ->with('user')
                ->when($this->search, function ($query) {
                    $query
                        ->where('phone_number', 'like', "%{$this->search}%")
                        ->orWhere('driver_license', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($q) {
                            $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
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
                    <th>Nama Pengemudi</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drivers as $driver)
                    <tr class="table-row" wire:key="driver-{{ $driver->id }}">
                        <td>{{ $drivers->firstItem() + $loop->index }}</td>
                        <td>{{ $driver->user->name }}</td>
                        <td>{{ $driver->user->email }}</td>
                        <td>{{ $driver->phone_number }}</td>
                        <td>
                            @if ($driver->user->photo)
                                <img src="{{ asset('storage/' . $driver->user->photo) }}"
                                    alt="{{ $driver->user->name }}"
                                    class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            @else
                                <span class="text-text-secondary">Tidak ada foto</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusBadge = match ($driver->available_status) {
                                    'Tersedia' => 'table-badge-success',
                                    'Sibuk' => 'table-badge-warning',
                                    'Libur' => 'table-badge-danger',
                                };
                            @endphp
                            <span class="table-badge {{ $statusBadge }}">{{ $driver->available_status }}</span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="editModal = true; editData = {
                                        id: {{ $driver->id }},
                                        name: @js($driver->user->name),
                                        username: @js($driver->user->username),
                                        email: @js($driver->user->email),
                                        phone_number: @js($driver->phone_number),
                                        driver_license: @js($driver->driver_license),
                                        available_status: @js($driver->available_status),
                                    }">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $driver->id }},
                                        name: @js($driver->user->name)
                                    }">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $drivers->firstItem() }}–{{ $drivers->lastItem() }} dari {{ $drivers->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $drivers->links('pagination.custom') }}
        </nav>
    </div>
</div>
