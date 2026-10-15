@php
    $statusBadge = match ($shipment->status) {
        'Sampai' => 'table-badge-primary',
        default => 'table-badge-warning',
    };
@endphp
<div class="bg-white rounded-xl shadow-md p-4 flex flex-col gap-3 border border-primary/40">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="font-display font-semibold text-text-primary text-sm">{{ $shipment->shipment_number }}</p>
            <p class="text-xs text-text-secondary">{{ $shipment->transaction?->transaction_number }}</p>
        </div>
        <span class="table-badge {{ $statusBadge }}">{{ $shipment->status }}</span>
    </div>

    <div class="space-y-1.5 text-sm">
        <div class="flex items-center gap-2 text-text-secondary">
            <i data-lucide="user" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="text-text-primary">{{ $shipment->transaction?->customer?->customer_name ?? '-' }}</span>
        </div>
        <div class="flex items-center gap-2 text-text-secondary">
            <i data-lucide="truck" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="text-text-primary">{{ $shipment->vehicle_number }}</span>
        </div>
        <div class="flex items-center gap-2 text-text-secondary">
            <i data-lucide="calendar" class="w-3.5 h-3.5 shrink-0"></i>
            <span
                class="text-text-primary">{{ optional($shipment->planned_shipped_at)->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <button type="button" class="btn btn-primary w-full mt-1"
        @click="viewModal = true; viewData = { id: {{ $shipment->id }} }">
        <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
        Lihat Detail
    </button>
</div>
