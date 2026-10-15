<?php

use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
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
            'shipments' => Shipment::query()
                ->with('transaction.customer', 'driver.user')
                ->when(Auth::user()->hasRole('pengemudi'), function ($query) {
                    $query
                        ->whereHas('driver', function ($d) {
                            $d->where('user_id', Auth::id());
                        })
                        ->whereDate('planned_shipped_at', '<=', now());
                })
                ->when(Auth::user()->hasRole('pelanggan'), function ($query) {
                    $query->whereHas('transaction.customer', function ($c) {
                        $c->where('user_id', Auth::id());
                    });
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('vehicle_number', 'like', "%{$this->search}%")
                            ->orWhere('shipment_number', 'like', "%{$this->search}%")
                            ->orWhereHas('transaction', function ($t) {
                                $t->where('transaction_number', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="flex flex-col gap-3">
    <div class="flex items-center flex-col md:flex-row justify-between gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" class="search-input w-full md:w-auto"
            placeholder="Cari...">
        @role('admin')
            <button class="btn btn-primary w-full md:w-auto" type="button" @click="createModal = true">Tambah</button>
        @endrole
    </div>

    <div class="table-wrapper" wire:loading.class="opacity-50">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th>No</th>
                    <th>No. Resi</th>
                    <th>No. Transaksi</th>
                    @role('admin')
                        <th>Pelanggan</th>
                    @endrole
                    <th>Pengemudi</th>
                    @hasanyrole(['admin', 'pengemudi'])
                        <th>Rencana Dikirim</th>
                    @endhasanyrole
                    @role('pelanggan')
                        <th>Plat Nomor</th>
                    @endrole
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shipments as $shipment)
                    <tr class="table-row" wire:key="shipment-{{ $shipment->id }}">
                        <td>{{ $shipments->firstItem() + $loop->index }}</td>
                        <td>{{ $shipment->shipment_number }}</td>
                        <td>{{ $shipment->transaction?->transaction_number }}</td>
                        @role('admin')
                            <td>{{ $shipment->transaction?->customer?->customer_name }}</td>
                        @endrole
                        <td>{{ $shipment->driver?->user?->name }}</td>
                        @hasanyrole(['admin', 'pengemudi'])
                            <td>{{ optional($shipment->planned_shipped_at)->translatedFormat('d M Y') }}</td>
                        @endhasanyrole
                        @role('pelanggan')
                            <td>{{ $shipment->vehicle_number }}</td>
                        @endrole
                        <td>
                            @php
                                $statusBadge = match ($shipment->status) {
                                    'Diterima' => 'table-badge-success',
                                    'Sampai' => 'table-badge-primary',
                                    default => 'table-badge-warning',
                                };
                            @endphp
                            <span class="table-badge {{ $statusBadge }}">{{ $shipment->status }}</span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                    @click="viewModal = true; viewData = { id: {{ $shipment->id }} }">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                    Lihat
                                </button>
                                @if ($shipment->status === 'Diterima')
                                    <a href="{{ route('shipments.surat-jalan', $shipment) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer">
                                        <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                                        PDF
                                    </a>
                                @endif
                                @role('pengemudi')
                                    @if ($shipment->status === 'Diproses')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                            @click="shipModal = true; shipData = {
                                            id: {{ $shipment->id }},
                                            shipment_number: @js($shipment->shipment_number),
                                            planned_shipped_at: @js(optional($shipment->planned_shipped_at)->format('Y-m-d')),
                                            planned_shipped_at_label: @js(optional($shipment->planned_shipped_at)->translatedFormat('d F Y')),
                                        }">
                                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                            Kirim
                                        </button>
                                    @endif
                                    @if ($shipment->status === 'Dikirim')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-primary hover:underline cursor-pointer"
                                            @click="arriveModal = true; arriveData = {
                                            id: {{ $shipment->id }},
                                            shipment_number: @js($shipment->shipment_number)
                                        }">
                                            <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                                            Tandai Sampai
                                        </button>
                                    @endif
                                @endrole
                                @role('pelanggan')
                                    @if ($shipment->status === 'Sampai')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-success hover:underline cursor-pointer"
                                            @click="receiveModal = true; receiveData = {
                                            id: {{ $shipment->id }},
                                            shipment_number: @js($shipment->shipment_number)
                                        }">
                                            <i data-lucide="circle-check" class="w-4 h-4 shrink-0"></i>
                                            Terima
                                        </button>
                                    @endif
                                @endrole
                                @if ($shipment->status == 'Diproses')
                                    @role('admin')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-warning hover:underline cursor-pointer"
                                            @click="editModal = true; editData = {
                                        id: {{ $shipment->id }},
                                        transaction_id: {{ $shipment->transaction_id }},
                                        transaction_number: @js($shipment->transaction?->transaction_number),
                                        driver_id: {{ $shipment->driver_id }},
                                        vehicle_number: @js($shipment->vehicle_number),
                                        status: @js($shipment->status),
                                    }">
                                            <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                            Edit
                                        </button>
                                    @endrole
                                    @role('admin')
                                        <button type="button" @disabled($shipment->status !== 'Diproses')
                                            class="inline-flex items-center gap-1 text-danger hover:underline cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed disabled:no-underline"
                                            @click="deleteModal = true; deleteData = {
                                        id: {{ $shipment->id }},
                                        shipment_number: @js($shipment->shipment_number)
                                    }">
                                            <i data-lucide="trash" class="w-4 h-4 shrink-0"></i>
                                            Delete
                                        </button>
                                    @endrole
                                @endif
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
        <span>Menampilkan {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }} dari {{ $shipments->total() }}
            data</span>
        <nav class="pagination-nav">
            {{ $shipments->links('pagination.custom') }}
        </nav>
    </div>
</div>
