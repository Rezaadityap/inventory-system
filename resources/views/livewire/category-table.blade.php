<?php

use App\Models\Category;
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
            'categories' => Category::query()
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('category_name', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between">
        <input type="search" wire:model.live.debounce.400ms="search" class="search-input" placeholder="Cari...">
        <button class="btn btn-primary" type="button" @click="createModal = true">Tambah</button>
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>Category Name</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="table-row" wire:key="category-{{ $category->id }}">
                        <td>{{ $categories->firstItem() + $loop->index }}</td>
                        <td>{{ $category->category_name }}</td>
                        <td>{{ $category->description }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="editModal = true; editData = {
                                        id: {{ $category->id }},
                                        category_name: @js($category->category_name),
                                        description: @js($category->description),
                                    }">
                                    <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                    Edit
                                </button>
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer"
                                    @click="deleteModal = true; deleteData = {
                                        id: {{ $category->id }},
                                        category_name: @js($category->category_name)
                                    }">
                                    <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-text-secondary">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span>Menampilkan {{ $categories->firstItem() }}–{{ $categories->lastItem() }} dari {{ $categories->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $categories->links('pagination.custom') }}
        </nav>
    </div>
</div>
