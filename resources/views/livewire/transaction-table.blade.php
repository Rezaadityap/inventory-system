<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function with()
    {
        return [
            'transactions' => Transaction::query()
                ->with('customer', 'items.waste', 'creditNote.retur.transaction')
                ->when(!Auth::user()->hasRole('admin'), function ($query) {
                    $query->whereHas('customer', function ($c) {
                        $c->where('user_id', Auth::id());
                    });
                })
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
                ->when($this->dateFrom, function ($query) {
                    $query->whereDate('created_at', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->whereDate('created_at', '<=', $this->dateTo);
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
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="dateFrom" class="form-input py-2 w-full sm:w-auto">
                <span class="text-text-secondary shrink-0">s/d</span>
                <input type="date" wire:model.live="dateTo" class="form-input py-2 w-full sm:w-auto">
            </div>
            <select wire:model.live="statusFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Status</option>
                <option value="Menunggu">Menunggu</option>
                <option value="Disetujui">Disetujui</option>
                <option value="Ditolak">Ditolak</option>
                <option value="Dikirim">Dikirim</option>
                <option value="Selesai">Selesai</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>
        </div>
        @role('pelanggan')
            <button class="btn btn-primary" type="button" @click="createModal = true">Tambah</button>
        @endrole
        @role('admin')
            <a class="btn btn-primary text-center"
                href="{{ route('transactions.export', ['search' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">Export</a>
        @endrole
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nomor Transaksi</th>
                    @role('admin')
                        <th>Customer</th>
                    @endrole
                    @role('pelanggan')
                        <th>Total Transaksi</th>
                    @endrole
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
                        @role('admin')
                            <td>{{ $transaction->customer->customer_name }}</td>
                        @endrole
                        @role('pelanggan')
                            <td>{{ $transaction->formattedPrice }}</td>
                        @endrole
                        <td>
                            @php
                                $statusBadge = match ($transaction->status) {
                                    'Disetujui', 'Selesai' => 'table-badge-success',
                                    'Ditolak', 'Dibatalkan' => 'table-badge-danger',
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
                                        name: @js($transaction->customer->user->name),
                                        status: @js($transaction->status),
                                        note: @js($transaction->note),
                                        for_date: @js(optional($transaction->for_date)->translatedFormat('d F Y')),
                                        requested_at: @js(optional($transaction->requested_at)->translatedFormat('d F Y')),
                                        approved_at: @js(optional($transaction->approved_at)->translatedFormat('d F Y')),
                                        rejected_at: @js(optional($transaction->rejected_at)->translatedFormat('d F Y')),
                                        admin_note: @js($transaction->admin_note),
                                        total_price: @js($transaction->total_price),
                                        credit_note_number: @js($transaction->creditNote?->credit_note_number),
                                        retur_number: @js($transaction->creditNote?->retur?->retur_number),
                                        parent_transaction_number: @js($transaction->creditNote?->retur?->transaction?->transaction_number),
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
                                @if ($transaction->status === 'Menunggu' && $transaction->customer->user_id === auth()->id())
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-warning hover:underline cursor-pointer"
                                        @click="editModal = true; editData = {
                                                id: {{ $transaction->id }},
                                                transaction_number: @js($transaction->transaction_number),
                                                name: @js($transaction->customer->user->name),
                                                customer_name: @js($transaction->customer->customer_name),
                                                requested_at: @js(optional($transaction->requested_at)->format('Y-m-d')),
                                                for_date: @js(optional($transaction->for_date)->format('Y-m-d')),
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
                                @role('pelanggan')
                                    @if ($transaction->status === 'Menunggu')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                            @click="cancelModal = true; cancelData = {
                                                id: {{ $transaction->id }},
                                                transaction_number: @js($transaction->transaction_number),
                                            }">
                                            <i data-lucide="x" class="w-4 h-4 shrink-0"></i>
                                            Batalkan
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
