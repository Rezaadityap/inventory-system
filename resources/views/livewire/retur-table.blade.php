<?php

use App\Models\Retur;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

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
            'returs' => Retur::query()
                ->with('customer', 'transaction', 'items.waste', 'creditNote.replacementTransaction')
                ->when(!Auth::user()->hasRole('admin'), function ($query) {
                    $query->whereHas('customer', function ($c) {
                        $c->where('user_id', Auth::id());
                    });
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('retur_number', 'like', "%{$this->search}%")
                            ->orWhereHas('transaction', function ($t) {
                                $t->where('transaction_number', 'like', "%{$this->search}%");
                            })
                            ->orWhereHas('customer', function ($c) {
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
                <option value="Selesai">Selesai</option>
                <option value="Ditolak">Ditolak</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>
        </div>
        @role('pelanggan')
            <button class="btn btn-primary" type="button" @click="createModal = true">Ajukan Retur</button>
        @endrole
        @role('admin')
            <a class="btn btn-primary text-center"
                href="{{ route('returs.export', ['search' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">Export</a>
        @endrole
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nomor Retur</th>
                    @role('admin')
                        <th>Customer</th>
                    @endrole
                    <th>Nomor Transaksi</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($returs as $retur)
                    <tr class="table-row" wire:key="retur-{{ $retur->id }}">
                        <td>{{ $returs->firstItem() + $loop->index }}</td>
                        <td>{{ $retur->retur_number }}</td>
                        @role('admin')
                            <td>{{ $retur->customer->customer_name }}</td>
                        @endrole
                        <td>{{ $retur->transaction->transaction_number }}</td>
                        <td>
                            @php
                                $statusBadge = match ($retur->status) {
                                    'Disetujui', 'Selesai' => 'table-badge-success',
                                    'Ditolak', 'Dibatalkan' => 'table-badge-danger',
                                    default => 'table-badge-warning',
                                };
                            @endphp
                            <span class="table-badge {{ $statusBadge }}">{{ $retur->status }}</span>
                        </td>
                        <td>{{ $retur->created_at->translatedFormat('d F Y') }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                        id: {{ $retur->id }},
                                        retur_number: @js($retur->retur_number),
                                        transaction_number: @js($retur->transaction->transaction_number),
                                        customer_name: @js($retur->customer->customer_name),
                                        status: @js($retur->status),
                                        note: @js($retur->note),
                                        requested_at: @js(optional($retur->requested_at)->translatedFormat('d F Y')),
                                        approved_at: @js(optional($retur->approved_at)->translatedFormat('d F Y')),
                                        rejected_at: @js(optional($retur->rejected_at)->translatedFormat('d F Y')),
                                        cancelled_at: @js(optional($retur->cancelled_at)->translatedFormat('d F Y')),
                                        admin_note: @js($retur->admin_note),
                                        credit_note: @js(
    $retur->creditNote
        ? [
            'number' => $retur->creditNote->credit_note_number,
            'subtotal' => 'Rp. ' . number_format($retur->creditNote->subtotal, 0, ',', '.'),
            'tax' => 'Rp. ' . number_format($retur->creditNote->tax, 0, ',', '.'),
            'total' => $retur->creditNote->formatted_total,
            'status' => $retur->creditNote->status,
            'status_label' => $retur->creditNote->status_label,
            'note' => $retur->creditNote->note,
            'refund_proof_url' => $retur->creditNote->refund_proof_url,
            'replacement_number' => $retur->creditNote->replacementTransaction?->transaction_number,
            'replacement_status' => $retur->creditNote->replacementTransaction?->status,
        ]
        : null,
),
                                        items: @js(
    $retur->items->map(
        fn($item) => [
            'waste_name' => $item->waste->waste_name,
            'qty' => $item->qty,
            'unit' => $item->waste->unit,
            'price' => 'Rp. ' . number_format($item->price, 0, ',', '.'),
            'note_item' => $item->note_item,
            'photo_url' => $item->photo_url,
        ],
    ),
),
                                    }">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                    Lihat
                                </button>
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
        <span>Menampilkan {{ $returs->firstItem() }}–{{ $returs->lastItem() }} dari
            {{ $returs->total() }} data</span>
        <nav class="pagination-nav">
            {{ $returs->links('pagination.custom') }}
        </nav>
    </div>
</div>
