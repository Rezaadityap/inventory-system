<?php

use App\Models\StockHistory;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
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

    public function updatingTypeFilter()
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
            'histories' => StockHistory::query()
                ->with('waste')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', 'like', "%{$this->search}%")
                            ->orWhere('source', 'like', "%{$this->search}%")
                            ->orWhereHas('waste', function ($w) {
                                $w->where('code', 'like', "%{$this->search}%")->orWhere('waste_name', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->when($this->typeFilter, function ($query) {
                    $query->where('type', $this->typeFilter);
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
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full sm:w-auto"
                placeholder="Cari...">
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="dateFrom" class="form-input py-2 w-full sm:w-auto">
                <span class="text-text-secondary shrink-0">s/d</span>
                <input type="date" wire:model.live="dateTo" class="form-input py-2 w-full sm:w-auto">
            </div>
            <select wire:model.live="typeFilter" class="form-input py-2 w-full sm:w-auto">
                <option value="">Semua Tipe</option>
                <option value="Masuk">Masuk</option>
                <option value="Keluar">Keluar</option>
            </select>
        </div>
        <a class="btn btn-primary text-center"
            href="{{ route('stock-histories.export', ['search' => $search, 'type' => $typeFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">Export</a>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Limbah</th>
                    <th>Tipe</th>
                    <th>Sumber</th>
                    <th>Qty</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($histories as $history)
                    <tr class="table-row" wire:key="history-{{ $history->id }}">
                        <td>{{ $histories->firstItem() + $loop->index }}</td>
                        <td>{{ $history->waste->code }}</td>
                        <td>{{ $history->waste->waste_name }}</td>
                        <td>
                            @if ($history->type === 'Masuk')
                                <span class="table-badge table-badge-success">{{ $history->type }}</span>
                            @else
                                <span class="table-badge table-badge-danger">{{ $history->type }}</span>
                            @endif
                        </td>
                        <td>{{ $history->source }}</td>
                        <td>{{ $history->qty }}</td>
                        <td>{{ $history->created_at->translatedFormat('d F Y') }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                        code: @js($history->waste->code),
                                        waste_name: @js($history->waste->waste_name),
                                        type: @js($history->type),
                                        source: @js($history->source),
                                        qty: @js($history->qty),
                                        name: @js($history->user->name),
                                        date: @js($history->created_at->translatedFormat('d F Y'))
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
        <span>Menampilkan {{ $histories->firstItem() }}–{{ $histories->lastItem() }} dari {{ $histories->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $histories->links('pagination.custom') }}
        </nav>
    </div>
</div>
