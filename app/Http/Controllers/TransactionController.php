<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('customer')->get();
        $wastes = Waste::orderBy('waste_name')->get();

        $customer = Customer::where('user_id', Auth::id())->first();
        $nextTransactionNumber = $customer ? $this->generateTransactionNumber($customer) : null;

        return view('transaction.index', compact('transactions', 'wastes', 'nextTransactionNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'note' => 'nullable|string',
            'waste_id.*' => 'required|exists:wastes,id',
            'qty.*' => 'required|integer|min:1',
            'note_item.*' => 'nullable|string',
        ]);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        $transaction = DB::transaction(function () use ($data, $customer, $request) {
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'transaction_number' => $this->generateTransactionNumber($customer),
                'status' => 'Pending',
                'note' => $data['note'] ?? null,
                'requested_at' => now()
            ]);

            foreach ($request->waste_id as $index => $wasteId) {
                $qty = (int) $request->qty[$index];

                $transaction->items()->create([
                    'waste_id' => $wasteId,
                    'qty' => $qty,
                    'note_item' => $request->note_item[$index] ?? null,
                ]);

                $this->adjustStock($transaction, $wasteId, $qty, 'Keluar', "Order {$transaction->transaction_number}");
            }

            return $transaction;
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil dibuat.");
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless(Auth::user()->hasRole('pelanggan'), 403);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        abort_unless($transaction->customer_id === $customer->id, 403);
        abort_unless($transaction->status === 'Pending', 403, 'Transaksi yang sudah diproses tidak bisa diedit.');

        $data = $request->validate([
            'note' => 'nullable|string',
            'item_id.*' => 'nullable|integer|exists:transaction_items,id',
            'waste_id.*' => 'required|exists:wastes,id',
            'qty.*' => 'required|integer|min:1',
            'note_item.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request, $transaction) {
            $transaction->update(['note' => $data['note'] ?? null]);

            $existingItems = $transaction->items->keyBy('id');
            $submittedIds = [];

            foreach ($request->waste_id as $index => $wasteId) {
                $itemId = $request->item_id[$index] ?? null;
                $qty = (int) $request->qty[$index];
                $noteItem = $request->note_item[$index] ?? null;

                // Item lama yang diedit — hanya sesuaikan stock kalau waste/qty-nya benar-benar berubah.
                if ($itemId && $existingItems->has((int) $itemId)) {
                    $existingItem = $existingItems->get((int) $itemId);
                    $submittedIds[] = $existingItem->id;

                    if ((int) $existingItem->waste_id !== (int) $wasteId) {
                        $this->adjustStock($transaction, $existingItem->waste_id, $existingItem->qty, 'Masuk', "Order {$transaction->transaction_number} (item diganti)");
                        $this->adjustStock($transaction, $wasteId, $qty, 'Keluar', "Order {$transaction->transaction_number} (item diganti)");
                    } elseif ((int) $existingItem->qty !== $qty) {
                        $delta = $qty - $existingItem->qty;

                        if ($delta > 0) {
                            $this->adjustStock($transaction, $wasteId, $delta, 'Keluar', "Order {$transaction->transaction_number} (tambah qty)");
                        } else {
                            $this->adjustStock($transaction, $wasteId, abs($delta), 'Masuk', "Order {$transaction->transaction_number} (kurangi qty)");
                        }
                    }

                    $existingItem->update([
                        'waste_id' => $wasteId,
                        'qty' => $qty,
                        'note_item' => $noteItem,
                    ]);

                    continue;
                }

                // Item baru yang ditambahkan saat edit.
                $transaction->items()->create([
                    'waste_id' => $wasteId,
                    'qty' => $qty,
                    'note_item' => $noteItem,
                ]);

                $this->adjustStock($transaction, $wasteId, $qty, 'Keluar', "Order {$transaction->transaction_number} (tambah item)");
            }

            // Item lama yang dihapus dari form.
            foreach ($existingItems as $existingItem) {
                if (!in_array($existingItem->id, $submittedIds, true)) {
                    $this->adjustStock($transaction, $existingItem->waste_id, $existingItem->qty, 'Masuk', "Order {$transaction->transaction_number} (item dihapus)");
                    $existingItem->delete();
                }
            }
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil diperbarui.");
    }

    public function approve(Transaction $transaction)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($transaction->status === 'Pending', 403, 'Transaksi ini sudah diproses.');

        $transaction->update([
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil disetujui.");
    }

    private function adjustStock(Transaction $transaction, int $wasteId, int $qty, string $type, string $remark): void
    {
        Stock::firstOrCreate(['waste_id' => $wasteId], ['qty' => 0]);

        $stock = Stock::where('waste_id', $wasteId)->lockForUpdate()->first();

        $qtyBefore = $stock->qty;
        $qtyAfter = $type === 'Keluar' ? $qtyBefore - $qty : $qtyBefore + $qty;

        if ($qtyAfter < 0) {
            throw ValidationException::withMessages([
                'qty' => "Stok tidak mencukupi untuk salah satu item. Sisa stok saat ini: {$qtyBefore}",
            ]);
        }

        $stock->update(['qty' => $qtyAfter]);

        StockHistory::create([
            'waste_id' => $wasteId,
            'transaction_id' => $transaction->id,
            'type' => $type,
            'source' => 'Order',
            'qty' => $qty,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'remark' => $remark,
            'user_id' => Auth::id(),
        ]);
    }

    private function generateTransactionNumber(Customer $customer): string
    {
        $prefix = 'TR-' . $this->customerCode($customer) . '-' . now()->format('Ymd') . '-';

        $lastNumber = Transaction::where('transaction_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function customerCode(Customer $customer): string
    {
        $initials = collect(preg_split('/\s+/', trim($customer->customer_name)))
            ->filter()
            ->map(fn($word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'CUST';
    }
}
