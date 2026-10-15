<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
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
            'customers' => Customer::query()
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('customer_name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%")
                            ->orWhere('phone_number', 'like', "%{$this->search}%");
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
                    <th>Nama Customer</th>
                    <th>Email</th>
                    <th>No. Hp</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr class="table-row" wire:key="customer-{{ $customer->id }}">
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td>{{ $customer->customer_name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone_number }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-warning hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                        id: {{ $customer->id }},
                                        customer_name: @js($customer->customer_name),
                                        email: @js($customer->email),
                                        phone_number: @js($customer->phone_number),
                                        address: @js($customer->address),
                                        company_photo: @js($customer->company_photo ? Storage::url($customer->company_photo) : null),
                                    }">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                    Detail
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="editModal = true; editData = {
                                        id: {{ $customer->id }},
                                        customer_name: @js($customer->customer_name),
                                        email: @js($customer->email),
                                        phone_number: @js($customer->phone_number),
                                        address: @js($customer->address),
                                        user_id: @js($customer->user_id)
                                    }">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $customer->id }},
                                        customer_name: @js($customer->customer_name)
                                    }">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $customers->firstItem() }}–{{ $customers->lastItem() }} dari {{ $customers->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $customers->links('pagination.custom') }}
        </nav>
    </div>
</div>
