<?php

use App\Models\Category;
use App\Models\Stock;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function with()
    {
        return [
            'stocks' => Stock::query()
                ->with('waste.category')
                ->when($this->search, function ($query) {
                    $query->whereHas('waste', function ($w) {
                        $w->where('code', 'like', "%{$this->search}%")->orWhere('waste_name', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->categoryFilter, function ($query) {
                    $query->whereHas('waste', function ($w) {
                        $w->where('category_id', $this->categoryFilter);
                    });
                })
                ->latest()
                ->paginate(10),
            'categories' => Category::orderBy('category_name')->get(),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex items-center flex-col md:flex-row justify-between gap-3">
        <div class="flex items-center flex-col sm:flex-row gap-3 w-full md:w-auto">
            <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full md:w-auto"
                placeholder="Cari...">
            <select wire:model.live="categoryFilter" class="form-input w-full md:w-auto">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary w-full md:w-auto" type="button" @click="createModal = true">Tambah</button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Limbah</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Qty</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $stock)
                    <tr class="table-row" wire:key="stock-{{ $stock->id }}">
                        <td>{{ $stocks->firstItem() + $loop->index }}</td>
                        <td>{{ $stock->waste->code }}</td>
                        <td>{{ $stock->waste->waste_name }}</td>
                        <td>{{ $stock->waste->category->category_name ?? '-' }}</td>
                        <td>{{ $stock->waste->unit }}</td>
                        <td>{{ $stock->qty }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = {
                                        code: @js($stock->waste->code),
                                        waste_name: @js($stock->waste->waste_name),
                                        category_name: @js($stock->waste->category->category_name ?? '-'),
                                        unit: @js($stock->waste->unit),
                                        qty: {{ $stock->qty }},
                                        updated_at: @js($stock->updated_at->diffForHumans()),
                                    }">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                    Lihat
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $stock->id }},
                                        waste_name: @js($stock->waste->waste_name)
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
        <span>Menampilkan {{ $stocks->firstItem() }}–{{ $stocks->lastItem() }} dari {{ $stocks->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $stocks->links('pagination.custom') }}
        </nav>
    </div>
</div>
