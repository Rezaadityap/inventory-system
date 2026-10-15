<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Retur;
use App\Models\Shipment;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = ['todayShipments' => collect()];

        if ($user->hasRole('pengemudi')) {
            $data['todayShipments'] = $this->driverShipments();
        }

        if ($user->hasRole('pelanggan')) {
            $data += $this->customerData();
        }

        if ($user->hasRole('admin')) {
            $data += $this->adminData();
        }

        return view('dashboard', $data);
    }

    private function adminData(): array
    {
        $now = now();
        $thisMonth = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $lastMonth = [$now->copy()->startOfMonth()->subMonth(), $now->copy()->startOfMonth()->subMonth()->endOfMonth()];

        $revenueThisMonth = $this->paidRevenue($thisMonth[0], $thisMonth[1]);
        $revenueLastMonth = $this->paidRevenue($lastMonth[0], $lastMonth[1]);

        $transactionThisMonth = Transaction::whereBetween('created_at', $thisMonth)->count();
        $transactionLastMonth = Transaction::whereBetween('created_at', $lastMonth)->count();

        $unpaidInvoices = Invoice::with('customer')
            ->where('status', 'unpaid')
            ->orderBy('due_date')
            ->get();

        $revenueMonths = collect(range(11, 0))->map(fn($ago) => $now->copy()->startOfMonth()->subMonths($ago));
        $stockMonths = collect(range(5, 0))->map(fn($ago) => $now->copy()->startOfMonth()->subMonths($ago));

        $revenuePerMonth = Invoice::where('status', 'paid')
            ->whereRaw('COALESCE(paid_at, created_at) >= ?', [$revenueMonths->first()])
            ->selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as period, SUM(total) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        $transactionPerMonth = Transaction::where('created_at', '>=', $revenueMonths->first())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        $stockPerMonth = StockHistory::where('created_at', '>=', $stockMonths->first())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, type, SUM(qty) as total")
            ->groupBy('period', 'type')
            ->get()
            ->groupBy('type');

        $statusCounts = Transaction::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusOrder = ['Menunggu', 'Disetujui', 'Ditolak', 'Dikirim', 'Selesai', 'Dibatalkan'];

        $topWastes = TransactionItem::join('wastes', 'wastes.id', '=', 'transaction_items.waste_id')
            ->selectRaw('wastes.waste_name as name, SUM(transaction_items.qty) as qty')
            ->groupBy('wastes.id', 'wastes.waste_name')
            ->orderByDesc('qty')
            ->take(5)
            ->get();

        return [
            'revenueThisMonth' => $revenueThisMonth,
            'revenueGrowth' => $this->growth($revenueThisMonth, $revenueLastMonth),
            'transactionThisMonth' => $transactionThisMonth,
            'transactionGrowth' => $this->growth($transactionThisMonth, $transactionLastMonth),
            'pendingApprovalCount' => Transaction::where('status', 'Menunggu')->count(),
            'pendingReturCount' => Retur::where('status', 'Menunggu')->count(),
            'receivableTotal' => (int) $unpaidInvoices->sum('total'),
            'overdueInvoiceCount' => $unpaidInvoices->filter(fn($invoice) => $invoice->isOverdue())->count(),
            'activeShipmentCount' => Shipment::whereIn('status', ['Diproses', 'Dikirim', 'Sampai'])->count(),
            'todayShipmentCount' => Shipment::whereDate('planned_shipped_at', $now)->count(),
            'customerCount' => Customer::count(),
            'revenueChart' => [
                'labels' => $revenueMonths->map(fn($month) => $month->translatedFormat('M Y'))->all(),
                'revenue' => $revenueMonths->map(fn($month) => (int) ($revenuePerMonth[$month->format('Y-m')] ?? 0))->all(),
                'transactions' => $revenueMonths->map(fn($month) => (int) ($transactionPerMonth[$month->format('Y-m')] ?? 0))->all(),
            ],
            'statusChart' => [
                'labels' => $statusOrder,
                'values' => collect($statusOrder)->map(fn($status) => (int) ($statusCounts[$status] ?? 0))->all(),
            ],
            'stockChart' => [
                'labels' => $stockMonths->map(fn($month) => $month->translatedFormat('M Y'))->all(),
                'in' => $this->stockSeries($stockPerMonth->get('Masuk'), $stockMonths),
                'out' => $this->stockSeries($stockPerMonth->get('Keluar'), $stockMonths),
            ],
            'topWasteChart' => [
                'labels' => $topWastes->pluck('name')->all(),
                'values' => $topWastes->pluck('qty')->map(fn($qty) => (int) $qty)->all(),
            ],
            'pendingTransactions' => Transaction::with('customer')
                ->where('status', 'Menunggu')
                ->oldest()
                ->take(5)
                ->get(),
            'pendingReturs' => Retur::with('customer')
                ->where('status', 'Menunggu')
                ->oldest()
                ->take(5)
                ->get(),
            'dueInvoices' => $unpaidInvoices->take(5),
            'lowStocks' => Stock::with('waste')
                ->orderBy('qty')
                ->take(5)
                ->get(),
        ];
    }

    private function paidRevenue(Carbon $from, Carbon $to): int
    {
        return (int) Invoice::where('status', 'paid')
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$from, $to])
            ->sum('total');
    }

    private function growth(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function stockSeries($rows, $months): array
    {
        $totals = collect($rows)->pluck('total', 'period');

        return $months->map(fn($month) => (int) ($totals[$month->format('Y-m')] ?? 0))->all();
    }

    private function driverShipments()
    {
        return Shipment::with('transaction.customer')
            ->whereHas('driver', fn($d) => $d->where('user_id', Auth::id()))
            ->whereIn('status', ['Diproses', 'Dikirim', 'Sampai'])
            ->whereDate('planned_shipped_at', now())
            ->orderBy('planned_shipped_at')
            ->get();
    }

    private function customerData(): array
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (! $customer) {
            return [
                'customer' => null,
                'unpaidInvoices' => collect(),
                'activeShipments' => collect(),
                'recentTransactions' => collect(),
                'unpaidTotal' => 0,
                'overdueCount' => 0,
                'activeOrderCount' => 0,
                'totalSpent' => 0,
            ];
        }

        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->orderBy('due_date')
            ->get();

        $activeShipments = Shipment::with('transaction')
            ->whereHas('transaction', fn($t) => $t->where('customer_id', $customer->id))
            ->whereIn('status', ['Diproses', 'Dikirim', 'Sampai'])
            ->orderByDesc('created_at')
            ->get();

        $recentTransactions = Transaction::where('customer_id', $customer->id)
            ->latest()
            ->take(5)
            ->get();

        return [
            'customer' => $customer,
            'unpaidInvoices' => $unpaidInvoices,
            'activeShipments' => $activeShipments,
            'recentTransactions' => $recentTransactions,
            'unpaidTotal' => (int) $unpaidInvoices->sum('total'),
            'overdueCount' => $unpaidInvoices->filter(fn($i) => $i->isOverdue())->count(),
            'activeOrderCount' => Transaction::where('customer_id', $customer->id)
                ->whereNotIn('status', ['Selesai', 'Ditolak', 'Dibatalkan'])
                ->count(),
            'totalSpent' => (int) Invoice::where('customer_id', $customer->id)
                ->where('status', 'paid')
                ->sum('total'),
        ];
    }
}
