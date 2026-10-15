@php
    $rupiah = fn($n) => 'Rp. ' . number_format((int) $n, 0, ',', '.');

    $shipmentSteps = ['Diproses', 'Dikirim', 'Sampai', 'Diterima'];

    $transactionBadge = [
        'Selesai' => 'table-badge-success',
        'Dibatalkan' => 'table-badge-danger',
        'Ditolak' => 'table-badge-danger',
        'Dikirim' => 'table-badge-primary',
        'Diproses' => 'table-badge-primary',
        'Disetujui' => 'table-badge-secondary',
    ];
@endphp

<div class="w-full space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div
            class="bg-white p-4 rounded-xl shadow-md flex items-start gap-3 {{ $overdueCount > 0 ? 'border border-danger/40' : '' }}">
            <div
                class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center {{ $overdueCount > 0 ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning' }}">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-text-secondary">Menunggu Pembayaran</p>
                <p class="text-xl font-semibold text-text-primary">{{ $unpaidInvoices->count() }}</p>
                <p class="text-xs text-text-secondary truncate">{{ $rupiah($unpaidTotal) }}</p>
                @if ($overdueCount > 0)
                    <p class="text-xs text-danger font-medium mt-0.5">{{ $overdueCount }} lewat jatuh tempo</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-md flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary shrink-0 flex items-center justify-center">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-text-secondary">Pesanan Aktif</p>
                <p class="text-xl font-semibold text-text-primary">{{ $activeOrderCount }}</p>
                <p class="text-xs text-text-secondary">Belum selesai</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-md flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-secondary/20 text-primary-dark shrink-0 flex items-center justify-center">
                <i data-lucide="truck" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-text-secondary">Dalam Pengiriman</p>
                <p class="text-xl font-semibold text-text-primary">{{ $activeShipments->count() }}</p>
                <p class="text-xs text-text-secondary">Sedang berjalan</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-md flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-success/10 text-success shrink-0 flex items-center justify-center">
                <i data-lucide="circle-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-text-secondary">Total Belanja</p>
                <p class="text-xl font-semibold text-text-primary truncate">{{ $rupiah($totalSpent) }}</p>
                <p class="text-xs text-text-secondary">Tagihan lunas</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h2 class="font-display font-semibold text-text-primary">Tagihan Menunggu Pembayaran</h2>
                @if ($unpaidInvoices->isNotEmpty())
                    <a href="{{ route('invoices.index') }}"
                        class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                        Bayar Sekarang
                        <i data-lucide="banknote" class="w-4 h-4 shrink-0"></i>
                    </a>
                @endif
            </div>

            @forelse ($unpaidInvoices as $invoice)
                @php $overdue = $invoice->isOverdue(); @endphp
                <div
                    class="flex items-center justify-between gap-3 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-text-primary truncate">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs {{ $overdue ? 'text-danger' : 'text-text-secondary' }} flex items-center gap-1 mt-0.5">
                            <i data-lucide="{{ $overdue ? 'alert-triangle' : 'calendar' }}" class="w-3.5 h-3.5 shrink-0"></i>
                            @if ($overdue)
                                Lewat jatuh tempo {{ optional($invoice->due_date)->translatedFormat('d F Y') }}
                            @else
                                Jatuh tempo {{ optional($invoice->due_date)->translatedFormat('d F Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-text-primary">{{ $rupiah($invoice->total) }}</p>
                        <span class="table-badge {{ $overdue ? 'table-badge-danger' : 'table-badge-warning' }} mt-1">
                            {{ $overdue ? 'Terlambat' : 'Belum Bayar' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center">
                    <div
                        class="w-12 h-12 rounded-full bg-success/10 text-success mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="circle-check" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-text-secondary">Tidak ada tagihan yang menunggu pembayaran.</p>
                </div>
            @endforelse
        </div>

        <div class="bg-white rounded-xl shadow-md p-5">
            <h2 class="font-display font-semibold text-text-primary mb-4">Pengiriman Berjalan</h2>

            @forelse ($activeShipments as $shipment)
                @php $step = array_search($shipment->status, $shipmentSteps, true); @endphp
                <div class="py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">{{ $shipment->shipment_number }}</p>
                            <p class="text-xs text-text-secondary truncate">
                                {{ $shipment->transaction?->transaction_number ?? '-' }}</p>
                        </div>
                        <span class="table-badge table-badge-primary shrink-0">{{ $shipment->status }}</span>
                    </div>

                    <div class="flex items-center gap-1 mt-3">
                        @foreach ($shipmentSteps as $index => $label)
                            <div class="h-1.5 flex-1 rounded-full {{ $index <= $step ? 'bg-primary' : 'bg-gray-200' }}"></div>
                        @endforeach
                    </div>

                    <p class="text-xs text-text-secondary mt-2 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 shrink-0"></i>
                        Estimasi tiba {{ optional($shipment->estimated_arrival)->translatedFormat('d F Y') ?? '-' }}
                    </p>
                </div>
            @empty
                <div class="py-10 text-center">
                    <div
                        class="w-12 h-12 rounded-full bg-gray-100 text-text-secondary mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="truck" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-text-secondary">Belum ada pengiriman berjalan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-5">
        <div class="flex items-center justify-between gap-2 mb-4">
            <h2 class="font-display font-semibold text-text-primary">Pesanan Terakhir</h2>
            <a href="{{ route('transactions.index') }}"
                class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                Lihat Semua
                <i data-lucide="table-of-contents" class="w-4 h-4 shrink-0"></i>
            </a>
        </div>

        @if ($recentTransactions->isEmpty())
            <div class="py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-text-secondary mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-text-secondary">Belum ada pesanan.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="table-base">
                    <thead class="table-head">
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $transaction)
                            <tr class="table-row">
                                <td>{{ $transaction->transaction_number }}</td>
                                <td>{{ optional($transaction->for_date)->translatedFormat('d M Y') ?? '-' }}</td>
                                <td>{{ $rupiah($transaction->total_price) }}</td>
                                <td>
                                    <span
                                        class="table-badge {{ $transactionBadge[$transaction->status] ?? 'table-badge-warning' }}">
                                        {{ $transaction->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
