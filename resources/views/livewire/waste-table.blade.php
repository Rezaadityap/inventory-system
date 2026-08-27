<?php

use App\Models\Category;
use App\Models\Waste;
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
            'wastes' => Waste::query()
                ->with('category')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('code', 'like', "%{$this->search}%")
                            ->orWhere('waste_name', 'like', "%{$this->search}%")
                            ->orWhere('unit', 'like', "%{$this->search}%")
                            ->orWhere('unit_price', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->categoryFilter, function ($query) {
                    $query->where('category_id', $this->categoryFilter);
                })
                ->orderBy('code')
                ->paginate(10),
            'categories' => Category::orderBy('category_name')->get(),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <input type="search" wire:model.live.debounce.400ms="search" class="search-input" placeholder="Cari...">
            <select wire:model.live="categoryFilter" class="form-input cursor-pointer py-2">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $item)
                    <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" type="button" @click="createModal = true">Tambah</button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Limbah</th>
                    <th>Unit</th>
                    <th>Harga</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($wastes as $waste)
                    <tr class="table-row" wire:key="waste-{{ $waste->id }}">
                        <td>{{ $wastes->firstItem() + $loop->index }}</td>
                        <td>{{ $waste->code }}</td>
                        <td>{{ $waste->waste_name }}</td>
                        <td>{{ $waste->unit }}</td>
                        <td>{{ $waste->formattedPrice }}</td>
                        <td>{{ $waste->category->category_name }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="editModal = true; editData = {
                                        id: {{ $waste->id }},
                                        code: @js($waste->code),
                                        waste_name: @js($waste->waste_name),
                                        unit: @js($waste->unit),
                                        unit_price: @js($waste->unit_price),
                                        category_id: @js($waste->category_id),
                                    }">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $waste->id }},
                                        waste_name: @js($waste->waste_name)
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
        <span>Menampilkan {{ $wastes->firstItem() }}–{{ $wastes->lastItem() }} dari {{ $wastes->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $wastes->links('pagination.custom') }}
        </nav>
    </div>
</div>
