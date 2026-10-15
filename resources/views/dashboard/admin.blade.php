@php
    $rupiah = fn($n) => 'Rp. ' . number_format((int) $n, 0, ',', '.');

    $growthMeta = function (?float $growth) {
        if ($growth === null) {
            return ['icon' => 'trending-up', 'class' => 'bg-primary/10 text-primary', 'label' => 'Baru'];
        }

        if ($growth > 0) {
            return ['icon' => 'trending-up', 'class' => 'bg-success/10 text-success', 'label' => '+' . $growth . '%'];
        }

        if ($growth < 0) {
            return ['icon' => 'trending-down', 'class' => 'bg-danger/10 text-danger', 'label' => $growth . '%'];
        }

        return ['icon' => 'minus', 'class' => 'bg-gray-100 text-text-secondary', 'label' => '0%'];
    };

    $revenueMeta = $growthMeta($revenueGrowth);
    $transactionMeta = $growthMeta($transactionGrowth);

    $hasTopWaste = count($topWasteChart['labels']) > 0;
    $hasStatus = array_sum($statusChart['values']) > 0;

    $chartPayload = json_encode(
        [
            'chart-revenue' => $revenueChart,
            'chart-status' => $statusChart,
            'chart-stock' => $stockChart,
            'chart-top-waste' => $topWasteChart,
        ],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
@endphp

<div class="w-full space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-text-secondary">Pendapatan Bulan Ini</p>
                    <p class="text-2xl font-display font-semibold text-text-primary mt-1 truncate">
                        {{ $rupiah($revenueThisMonth) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $revenueMeta['class'] }}">
                    <i data-lucide="{{ $revenueMeta['icon'] }}" class="w-3.5 h-3.5 shrink-0"></i>
                    {{ $revenueMeta['label'] }}
                </span>
                <span class="text-xs text-text-secondary">vs bulan lalu</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-text-secondary">Transaksi Bulan Ini</p>
                    <p class="text-2xl font-display font-semibold text-text-primary mt-1">{{ $transactionThisMonth }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-secondary/20 text-primary-dark flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $transactionMeta['class'] }}">
                    <i data-lucide="{{ $transactionMeta['icon'] }}" class="w-3.5 h-3.5 shrink-0"></i>
                    {{ $transactionMeta['label'] }}
                </span>
                <span class="text-xs text-text-secondary">{{ $pendingApprovalCount }} perlu persetujuan</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md {{ $overdueInvoiceCount > 0 ? 'border border-danger/40' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-text-secondary">Piutang Berjalan</p>
                    <p class="text-2xl font-display font-semibold text-text-primary mt-1 truncate">
                        {{ $rupiah($receivableTotal) }}</p>
                </div>
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $overdueInvoiceCount > 0 ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning' }}">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                @if ($overdueInvoiceCount > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-danger/10 text-danger">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                        {{ $overdueInvoiceCount }} terlambat
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">
                        <i data-lucide="circle-check" class="w-3.5 h-3.5 shrink-0"></i>
                        Aman
                    </span>
                @endif
                <span class="text-xs text-text-secondary">belum dibayar</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-text-secondary">Pengiriman Berjalan</p>
                    <p class="text-2xl font-display font-semibold text-text-primary mt-1">{{ $activeShipmentCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-success/10 text-success flex items-center justify-center shrink-0">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 shrink-0"></i>
                    {{ $todayShipmentCount }} hari ini
                </span>
                <span class="text-xs text-text-secondary">{{ $customerCount }} pelanggan</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-5">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="font-display font-semibold text-text-primary">Tren Pendapatan &amp; Transaksi</h2>
                    <p class="text-xs text-text-secondary mt-0.5">12 bulan terakhir</p>
                </div>
                <a href="{{ route('invoices.index') }}"
                    class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                    Invoice
                    <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                </a>
            </div>
            <div class="h-72">
                <canvas id="chart-revenue"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="mb-4">
                <h2 class="font-display font-semibold text-text-primary">Status Transaksi</h2>
                <p class="text-xs text-text-secondary mt-0.5">Seluruh periode</p>
            </div>
            @if ($hasStatus)
                <div class="h-72">
                    <canvas id="chart-status"></canvas>
                </div>
            @else
                <div class="h-72 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-text-secondary flex items-center justify-center mb-3">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-text-secondary">Belum ada transaksi.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="font-display font-semibold text-text-primary">Pergerakan Stok</h2>
                    <p class="text-xs text-text-secondary mt-0.5">Masuk vs keluar, 6 bulan terakhir</p>
                </div>
                <a href="{{ route('stock-histories.index') }}"
                    class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                    Riwayat
                    <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                </a>
            </div>
            <div class="h-64">
                <canvas id="chart-stock"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="font-display font-semibold text-text-primary">Limbah Terbanyak Diminta</h2>
                    <p class="text-xs text-text-secondary mt-0.5">Top 5 berdasarkan kuantitas</p>
                </div>
                <a href="{{ route('wastes.index') }}"
                    class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                    Limbah
                    <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                </a>
            </div>
            @if ($hasTopWaste)
                <div class="h-64">
                    <canvas id="chart-top-waste"></canvas>
                </div>
            @else
                <div class="h-64 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-text-secondary flex items-center justify-center mb-3">
                        <i data-lucide="flask-conical" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-text-secondary">Belum ada permintaan limbah.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-5 flex flex-col">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h2 class="font-display font-semibold text-text-primary">Menunggu Persetujuan</h2>
                <a href="{{ route('transactions.index') }}"
                    class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                    Lihat Semua
                    <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                </a>
            </div>

            @if ($pendingTransactions->isEmpty())
                <div class="flex-1 py-10 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-success/10 text-success flex items-center justify-center mb-3">
                        <i data-lucide="circle-check" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-text-secondary">Semua transaksi sudah diproses.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th>Nomor Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Untuk Tanggal</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingTransactions as $transaction)
                                <tr class="table-row">
                                    <td>{{ $transaction->transaction_number }}</td>
                                    <td>{{ $transaction->customer?->customer_name ?? '-' }}</td>
                                    <td>{{ optional($transaction->for_date)->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>{{ $rupiah($transaction->total_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-md p-5">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h2 class="font-display font-semibold text-text-primary">Retur Menunggu</h2>
                    <a href="{{ route('returs.index') }}"
                        class="text-sm text-primary hover:underline inline-flex items-center gap-1 shrink-0">
                        Lihat Semua
                        <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                    </a>
                </div>

                @forelse ($pendingReturs as $retur)
                    <div class="flex items-center justify-between gap-3 py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">{{ $retur->retur_number }}</p>
                            <p class="text-xs text-text-secondary truncate mt-0.5">
                                {{ $retur->customer?->customer_name ?? '-' }} ·
                                {{ optional($retur->requested_at)->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>
                        <span class="table-badge table-badge-warning shrink-0">Menunggu</span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-text-secondary">Tidak ada retur yang menunggu.</p>
                    </div>
                @endforelse
            </div>

            <div class="bg-white rounded-xl shadow-md p-5">
                <h2 class="font-display font-semibold text-text-primary mb-4">Tagihan Terdekat</h2>

                @forelse ($dueInvoices as $invoice)
                    @php $overdue = $invoice->isOverdue(); @endphp
                    <div class="flex items-center justify-between gap-3 py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs {{ $overdue ? 'text-danger' : 'text-text-secondary' }} truncate mt-0.5">
                                {{ $invoice->customer?->customer_name ?? '-' }} ·
                                {{ optional($invoice->due_date)->translatedFormat('d M Y') ?? '-' }}
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
                    <div class="py-8 text-center">
                        <p class="text-sm text-text-secondary">Tidak ada tagihan tertunggak.</p>
                    </div>
                @endforelse
            </div>

            <div class="bg-white rounded-xl shadow-md p-5">
                <h2 class="font-display font-semibold text-text-primary mb-4">Stok Terendah</h2>

                @forelse ($lowStocks as $stock)
                    <div class="flex items-center justify-between gap-3 py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">
                                {{ $stock->waste?->waste_name ?? '-' }}</p>
                            <p class="text-xs text-text-secondary truncate mt-0.5">{{ $stock->waste?->code ?? '-' }}</p>
                        </div>
                        <span class="table-badge {{ $stock->qty <= 0 ? 'table-badge-danger' : ($stock->qty < 10 ? 'table-badge-warning' : 'table-badge-primary') }} shrink-0">
                            {{ $stock->qty }} {{ $stock->waste?->unit }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-text-secondary">Belum ada data stok.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="dashboard-chart-data">{!! $chartPayload !!}</script>
