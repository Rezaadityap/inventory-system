<?php

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $monthFilter = '';
    public string $yearFilter = '';

    public function mount()
    {
        $this->monthFilter = (string) now()->month;
        $this->yearFilter = (string) now()->year;
    }

    // Untuk merefresh table setelah pembayaran berhasil dilakukan
    public function refresh(): void
    {
        //
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
    {
        $this->resetPage();
    }

    public function with()
    {
        $oldestDate = Invoice::min('created_at');
        $oldestYear = $oldestDate ? Carbon::parse($oldestDate)->year : now()->year;

        return [
            'months' => collect(range(1, 12))->mapWithKeys(fn($month) => [$month => Carbon::create(null, $month, 1)->translatedFormat('F')]),
            'years' => range(now()->year, min($oldestYear, now()->year)),
            'invoices' => Invoice::query()
                ->with('customer', 'transaction.items.waste', 'creditNotes.retur')
                ->when(!Auth::user()->hasRole('admin'), function ($query) {
                    $query->whereHas('customer', function ($c) {
                        $c->where('user_id', Auth::id());
                    });
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('invoice_number', 'like', "%{$this->search}%")
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
                ->when($this->monthFilter, function ($query) {
                    $query->whereMonth('created_at', $this->monthFilter);
                })
                ->when($this->yearFilter, function ($query) {
                    $query->whereYear('created_at', $this->yearFilter);
                })
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full sm:w-auto"
                placeholder="Cari...">
            <select wire:model.live="monthFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Bulan</option>
                @foreach ($months as $number => $label)
                    <option value="{{ $number }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="yearFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Tahun</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Bayar</option>
                <option value="paid">Lunas</option>
                <option value="cancelled">Dibatalkan</option>
                <option value="expired">Kedaluwarsa</option>
            </select>
        </div>
        <a class="btn btn-primary text-center"
            href="{{ route('invoices.export', ['search' => $search, 'status' => $statusFilter, 'month' => $monthFilter, 'year' => $yearFilter]) }}">Export</a>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Nomor Invoice</th>
                    <th>Nomor Transaksi</th>
                    @role('admin')
                        <th>Customer</th>
                    @endrole
                    <th>Total</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr class="table-row" wire:key="invoice-{{ $invoice->id }}">
                        <td>{{ $invoices->firstItem() + $loop->index }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->transaction?->transaction_number ?? '-' }}</td>
                        @role('admin')
                            <td>{{ $invoice->customer?->customer_name ?? '-' }}</td>
                        @endrole
                        <td>{{ $invoice->formattedTotal }}</td>
                        <td>{{ optional($invoice->due_date)->translatedFormat('d F Y') ?? '-' }}</td>
                        <td>
                            @php
                                $statusBadge = match ($invoice->status) {
                                    'paid' => 'table-badge-success',
                                    'cancelled', 'expired' => 'table-badge-danger',
                                    default => 'table-badge-warning',
                                };
                            @endphp
                            <span class="table-badge {{ $statusBadge }}">{{ $invoice->statusLabel }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($invoice->isPayable() && auth()->user()->hasRole('pelanggan'))
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                        @click="payModal = true; payData = {
                                            id: {{ $invoice->id }},
                                            invoice_number: @js($invoice->invoice_number),
                                            due_date: @js(optional($invoice->due_date)->translatedFormat('d F Y')),
                                            total: {{ (int) $invoice->total }},
                                        }">
                                        <i data-lucide="banknote" class="w-4 h-4 shrink-0"></i>
                                        Bayar
                                    </button>
                                @endif
                                @if ($invoice->status === 'paid')
                                    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-success hover:underline cursor-pointer">
                                        <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                                        Invoice
                                    </a>
                                @endif
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                    invoice_number: @js($invoice->invoice_number),
                                    transaction_number: @js($invoice->transaction?->transaction_number),
                                    customer_name: @js($invoice->customer?->customer_name),
                                    status: @js($invoice->statusLabel),
                                    due_date: @js(optional($invoice->due_date)->translatedFormat('d F Y')),
                                    paid_at: @js(optional($invoice->paid_at)->translatedFormat('d F Y H:i')),
                                    subtotal: {{ (int) $invoice->subtotal }},
                                    discount: {{ (int) $invoice->discount }},
                                    tax: {{ (int) $invoice->tax }},
                                    total: {{ (int) $invoice->total }},
                                    credit_notes: @js(
    $invoice->creditNotes->map(
        fn($cn) => [
            'number' => $cn->credit_note_number,
            'retur_number' => $cn->retur?->retur_number,
            'total' => $cn->formatted_total,
            'status_label' => $cn->status_label,
            'status' => $cn->status,
        ],
    ),
),
                                    items: @js(
    $invoice->transaction?->items->map(
        fn($item) => [
            'waste_name' => $item->waste?->waste_name,
            'qty' => $item->qty,
            'unit' => $item->waste?->unit,
            'price' => $item->price,
            'note_item' => $item->note_item,
        ],
    ) ?? [],
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
                        <td colspan="8" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} dari
            {{ $invoices->total() }} data</span>
        <nav class="pagination-nav">
            {{ $invoices->links('pagination.custom') }}
        </nav>
    </div>
</div>
