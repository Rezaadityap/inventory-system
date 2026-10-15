<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Waste;
use App\Notifications\NewTransactionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'for_date' => 'required|date',
            'waste_id.*' => 'required|exists:wastes,id',
            'qty.*' => 'required|integer|min:1',
            'note_item.*' => 'nullable|string',
        ]);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        $transaction = DB::transaction(function () use ($data, $customer, $request) {
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'transaction_number' => $this->generateTransactionNumber($customer),
                'total_price' => 0,
                'status' => 'Menunggu',
                'note' => $data['note'] ?? null,
                'for_date' => $data['for_date'] ?? null,
                'requested_at' => now()
            ]);

            $wastePrices = Waste::whereIn('id', $request->waste_id)->pluck('unit_price', 'id');
            $totalPrice = 0;

            foreach ($request->waste_id as $index => $wasteId) {
                $qty = (int) $request->qty[$index];
                $itemPrice = (int) ($wastePrices[$wasteId] ?? 0) * $qty;
                $totalPrice += $itemPrice;

                $transaction->items()->create([
                    'waste_id' => $wasteId,
                    'qty' => $qty,
                    'price' => $itemPrice,
                    'note_item' => $request->note_item[$index] ?? null,
                ]);
            }

            $transaction->update(['total_price' => $totalPrice]);

            $this->recordHistory($transaction, 'Menunggu', 'Transaksi dibuat oleh pelanggan.');

            return $transaction;
        });

        $transaction->load('customer', 'items.waste');

        $admins = User::withRole('admin')->get();
        Notification::send($admins, new NewTransactionNotification($transaction));

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil dibuat.");
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->customer->user_id === Auth::id(), 403, 'Hanya pembuat transaksi yang bisa mengedit.');
        abort_unless($transaction->status === 'Menunggu', 403, 'Transaksi yang sudah diproses tidak bisa diedit.');

        $data = $request->validate([
            'note' => 'nullable|string',
            'for_date' => 'required|date',
            'item_id.*' => 'nullable|integer|exists:transaction_items,id',
            'waste_id.*' => 'required|exists:wastes,id',
            'qty.*' => 'required|integer|min:1',
            'note_item.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request, $transaction) {
            $transaction->update([
                'note' => $data['note'] ?? null,
                'for_date' => $data['for_date'] ?? null,
            ]);

            $existingItems = $transaction->items->keyBy('id');
            $submittedIds = [];

            $wastePrices = Waste::whereIn('id', $request->waste_id)->pluck('unit_price', 'id');

            foreach ($request->waste_id as $index => $wasteId) {
                $itemId = $request->item_id[$index] ?? null;
                $qty = (int) $request->qty[$index];
                $noteItem = $request->note_item[$index] ?? null;
                $itemPrice = (int) ($wastePrices[$wasteId] ?? 0) * $qty;

                // Stok belum berkurang selama transaksi masih Pending, jadi edit item tidak menyentuh stok.
                if ($itemId && $existingItems->has((int) $itemId)) {
                    $existingItem = $existingItems->get((int) $itemId);
                    $submittedIds[] = $existingItem->id;

                    $existingItem->update([
                        'waste_id' => $wasteId,
                        'qty' => $qty,
                        'price' => $itemPrice,
                        'note_item' => $noteItem,
                    ]);

                    continue;
                }

                $transaction->items()->create([
                    'waste_id' => $wasteId,
                    'qty' => $qty,
                    'price' => $itemPrice,
                    'note_item' => $noteItem,
                ]);
            }

            // Item lama yang dihapus dari form.
            foreach ($existingItems as $existingItem) {
                if (!in_array($existingItem->id, $submittedIds, true)) {
                    $existingItem->delete();
                }
            }

            $transaction->update(['total_price' => $transaction->items()->sum('price')]);

            $this->recordHistory($transaction, $transaction->status, 'Transaksi diperbarui oleh pelanggan.');
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil diperbarui.");
    }

    public function approve(Request $request, Transaction $transaction)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($transaction->status === 'Menunggu', 403, 'Transaksi ini sudah diproses.');

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $note = $data['admin_note'] ?? null;

        DB::transaction(function () use ($transaction, $note) {
            $transaction->update([
                'status' => 'Disetujui',
                'admin_note' => $note,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            $description = 'Transaksi disetujui oleh admin.' . ($note ? " Keterangan: {$note}" : '');

            $this->recordHistory($transaction, 'Disetujui', $description);
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil disetujui.");
    }

    public function reject(Request $request, Transaction $transaction)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($transaction->status === 'Menunggu', 403, 'Transaksi ini sudah diproses.');

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $note = $data['admin_note'] ?? null;

        DB::transaction(function () use ($transaction, $note) {
            $transaction->update([
                'status' => 'Ditolak',
                'admin_note' => $note,
                'rejected_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            $description = 'Transaksi ditolak oleh admin.' . ($note ? " Alasan: {$note}" : '');

            $this->recordHistory($transaction, 'Ditolak', $description);
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil ditolak.");
    }

    public function cancel(Transaction $transaction)
    {
        abort_unless(Auth::user()->hasRole('pelanggan'), 403);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        abort_unless($transaction->customer_id === $customer->id, 403);

        abort_unless($transaction->status === 'Menunggu', 403, 'Transaksi yang sudah diproses tidak bisa dibatalkan.');

        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => 'Dibatalkan',
                'cancelled_at' => now(),
            ]);

            $this->recordHistory($transaction, 'Dibatalkan', 'Transaksi dibatalkan oleh pelanggan.');
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil dibatalkan.");
    }

    private function recordHistory(Transaction $transaction, string $status, string $description, ?string $photoPath = null): void
    {
        $transaction->histories()->create([
            'status' => $status,
            'description' => $description,
            'photo_path' => $photoPath,
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

    public function export(Request $request)
    {
        $transactions = Transaction::query()
            ->with('customer', 'items.waste')
            ->when(!Auth::user()->hasRole('admin'), function ($query) {
                $query->whereHas('customer', function ($c) {
                    $c->where('user_id', Auth::id());
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('transaction_number', 'like', "%{$request->search}%")
                        ->orWhereHas('customer', function ($c) use ($request) {
                            $c->where('customer_name', 'like', "%{$request->search}%");
                        });
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date_from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->get();

        if ($transactions->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi yang bisa diexport untuk filter yang dipilih.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Transaksi');

        $headers = ['No', 'Nomor Transaksi', 'Pelanggan', 'Tanggal Minta', 'Untuk Tanggal', 'Kode', 'Nama Limbah', 'Qty', 'Status', 'Keterangan'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $row = 2;
        foreach ($transactions as $index => $transaction) {
            foreach ($transaction->items as $item) {
                $sheet->fromArray([
                    $index + 1,
                    $transaction->transaction_number,
                    $transaction->customer->customer_name,
                    optional($transaction->requested_at)->translatedFormat('d F Y'),
                    optional($transaction->for_date)->translatedFormat('d F Y'),
                    $item->waste->code,
                    $item->waste->waste_name,
                    $item->qty,
                    $transaction->status,
                    $item->note_item ?? $transaction->note,
                ], null, "A{$row}");
                $row++;
            }
        }

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'riwayat-transaksi-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
