<?php

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function with()
    {
        return [
            'transactions' => Transaction::query()
                ->with('customer', 'items.waste')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('transaction_number', 'like', "%{$this->search}%")->orWhereHas('customer', function ($c) {
                            $c->where('customer_name', 'like', "%{$this->search}%");
                        });
                    });
                })
                ->when($this->statusFilter, function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full sm:w-auto"
                placeholder="Cari...">
            <select wire:model.live="statusFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Processing">Processing</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        @role('pelanggan')
            <button class="btn btn-primary" type="button" @click="createModal = true">Tambah</button>
        @endrole
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nomor Transaksi</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="table-row" wire:key="transaction-{{ $transaction->id }}">
                        <td>{{ $transactions->firstItem() + $loop->index }}</td>
                        <td>{{ $transaction->transaction_number }}</td>
                        <td>{{ $transaction->customer->customer_name }}</td>
                        <td>
                            @php
                                $statusBadge = match ($transaction->status) {
                                    'Approved', 'Completed' => 'table-badge-success',
                                    'Cancelled' => 'table-badge-danger',
                                    default => 'table-badge-warning',
                                };
                            @endphp
                            <span class="table-badge {{ $statusBadge }}">{{ $transaction->status }}</span>
                        </td>
                        <td>{{ $transaction->created_at->translatedFormat('d F Y') }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                        id: {{ $transaction->id }},
                                        transaction_number: @js($transaction->transaction_number),
                                        customer_name: @js($transaction->customer->customer_name),
                                        status: @js($transaction->status),
                                        note: @js($transaction->note),
                                        requested_at: @js(optional($transaction->requested_at)->translatedFormat('d F Y')),
                                        total_price: @js($transaction->total_price),
                                        items: @js(
    $transaction->items->map(
        fn($item) => [
            'waste_name' => $item->waste->waste_name,
            'qty' => $item->qty,
            'unit' => $item->waste->unit,
            'price' => $item->price,
            'note_item' => $item->note_item,
        ],
    ),
),
                                    }">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                    Lihat
                                </button>
                                @role('pelanggan')
                                    @if ($transaction->status === 'Pending')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-warning hover:underline cursor-pointer"
                                            @click="editModal = true; editData = {
                                                id: {{ $transaction->id }},
                                                transaction_number: @js($transaction->transaction_number),
                                                customer_name: @js($transaction->customer->customer_name),
                                                requested_at: @js(optional($transaction->requested_at)->format('Y-m-d')),
                                                note: @js($transaction->note),
                                                items: @js(
    $transaction->items->map(
        fn($item) => [
            'id' => $item->id,
            'waste_id' => $item->waste_id,
            'qty' => $item->qty,
            'note_item' => $item->note_item,
        ],
    ),
),
                                            }">
                                            <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                            Edit
                                        </button>
                                    @endif
                                @endrole
                                @role('admin')
                                    @if ($transaction->status === 'Pending')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-success hover:underline cursor-pointer"
                                            @click="approveModal = true; approveData = {
                                                id: {{ $transaction->id }},
                                                transaction_number: @js($transaction->transaction_number),
                                            }">
                                            <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                                            Approve
                                        </button>
                                    @endif
                                @endrole
                            </div>
                        </td>
                    </tr>
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

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari
            {{ $transactions->total() }} data</span>
        <nav class="pagination-nav">
            {{ $transactions->links('pagination.custom') }}
        </nav>
    </div>
</div>
