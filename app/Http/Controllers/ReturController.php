<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Retur;
use App\Models\ReturItem;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Notifications\NewReturNotification;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReturController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        $eligibleTransactions = $customer
            ? Transaction::where('customer_id', $customer->id)
            ->where('status', 'Selesai')
            ->with('items.waste')
            ->latest()
            ->get()
            : collect();

        $nextReturNumber = $customer ? $this->generateReturNumber($customer) : null;

        return view('retur.index', compact('eligibleTransactions', 'nextReturNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'note' => 'nullable|string',
            'transaction_item_id.*' => 'required|exists:transaction_items,id',
            'qty.*' => 'required|integer|min:1',
            'note_item.*' => 'nullable|string',
            'photo' => 'required|array',
            'photo.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'photo.required' => 'Foto bukti wajib diunggah untuk setiap item yang diretur.',
            'photo.*.image' => 'Bukti retur harus berupa gambar.',
            'photo.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.*.max' => 'Ukuran setiap foto maksimal 2 MB.',
        ]);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        $transaction = Transaction::where('id', $data['transaction_id'])
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        abort_unless($transaction->status === 'Selesai', 403, 'Retur hanya bisa diajukan untuk transaksi yang sudah selesai.');

        $storedPaths = [];

        try {
            $retur = DB::transaction(function () use ($data, $customer, $transaction, $request, &$storedPaths) {
                $retur = Retur::create([
                    'customer_id' => $customer->id,
                    'transaction_id' => $transaction->id,
                    'retur_number' => $this->generateReturNumber($customer),
                    'status' => 'Menunggu',
                    'note' => $data['note'] ?? null,
                    'requested_at' => now(),
                ]);

                foreach ($request->transaction_item_id as $index => $itemId) {
                    $transactionItem = TransactionItem::where('id', $itemId)
                        ->where('transaction_id', $transaction->id)
                        ->firstOrFail();

                    $qty = (int) $request->qty[$index];

                    $alreadyReturned = ReturItem::where('transaction_item_id', $transactionItem->id)
                        ->whereHas('retur', fn($q) => $q->whereNotIn('status', ['Ditolak', 'Dibatalkan']))
                        ->sum('qty');

                    $remaining = $transactionItem->qty - $alreadyReturned;

                    if ($qty > $remaining) {
                        throw ValidationException::withMessages([
                            'qty' => "Qty retur untuk {$transactionItem->waste->waste_name} melebihi sisa qty yang bisa diretur ({$remaining}).",
                        ]);
                    }

                    $photo = $request->file('photo')[$index] ?? null;

                    if (!$photo) {
                        throw ValidationException::withMessages([
                            'photo' => "Foto bukti wajib diunggah untuk {$transactionItem->waste->waste_name}.",
                        ]);
                    }

                    $photoPath = $photo->store('retur_photos', 'public');
                    $storedPaths[] = $photoPath;

                    $retur->items()->create([
                        'transaction_item_id' => $transactionItem->id,
                        'waste_id' => $transactionItem->waste_id,
                        'qty' => $qty,
                        'price' => $this->itemValue($transactionItem, $qty),
                        'note_item' => $request->note_item[$index] ?? null,
                        'photo_path' => $photoPath,
                    ]);
                }

                return $retur;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);

            throw $e;
        }

        $this->recordTransactionHistory($transaction, "Retur {$retur->retur_number} diajukan oleh pelanggan.");

        $retur->load('customer', 'transaction', 'items.waste');

        $admins = User::withRole('admin')->get();
        Notification::send($admins, new NewReturNotification($retur));

        return back()->with('success', "Retur {$retur->retur_number} berhasil diajukan.");
    }

    public function approve(Request $request, Retur $retur)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($retur->status === 'Menunggu', 403, 'Retur ini sudah diproses.');

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $note = $data['admin_note'] ?? null;

        $creditNote = DB::transaction(function () use ($retur, $note) {
            foreach ($retur->items as $item) {
                Stock::firstOrCreate(['waste_id' => $item->waste_id], ['qty' => 0]);

                $stock = Stock::where('waste_id', $item->waste_id)
                    ->lockForUpdate()
                    ->first();

                $qtyBefore = $stock->qty;
                $qtyAfter = $qtyBefore + $item->qty;

                $stock->update(['qty' => $qtyAfter]);

                StockHistory::create([
                    'waste_id' => $item->waste_id,
                    'transaction_id' => $retur->transaction_id,
                    'type' => 'Masuk',
                    'source' => 'Retur',
                    'qty' => $item->qty,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'remark' => "Retur {$retur->retur_number}",
                    'user_id' => Auth::id(),
                ]);
            }

            $retur->update([
                'status' => 'Disetujui',
                'admin_note' => $note,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            $creditNote = $this->invoiceService->createCreditNoteFromRetur($retur);

            if ($creditNote->status === 'applied') {
                $retur->update([
                    'status' => 'Selesai',
                    'completed_at' => now(),
                ]);
            }

            $description = "Retur {$retur->retur_number} disetujui, nota kredit {$creditNote->credit_note_number} diterbitkan";
            $description .= $creditNote->status === 'applied'
                ? ' dan langsung dipotong dari invoice.'
                : ' dan menunggu pengembalian dana.';

            $this->recordTransactionHistory($retur->transaction, $description);

            return $creditNote;
        });

        $message = "Retur {$retur->retur_number} berhasil disetujui. Nota kredit {$creditNote->credit_note_number} sebesar {$creditNote->formatted_total} diterbitkan";

        $message .= $creditNote->status === 'applied'
            ? ' dan langsung dipotong dari invoice.'
            : ' dan menunggu pengembalian dana.';

        return back()->with('success', $message);
    }

    public function settle(Request $request, Retur $retur)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($retur->status === 'Disetujui', 403, 'Retur ini belum disetujui atau sudah selesai.');

        $creditNote = $retur->creditNote;

        abort_unless($creditNote !== null, 404, 'Retur ini belum punya nota kredit.');
        abort_unless($creditNote->status === 'open', 403, 'Nota kredit ini sudah diselesaikan.');

        $data = $request->validate([
            'method' => 'required|in:refund,replace',
            'note' => 'nullable|string|max:1000',
            'refund_proof' => 'required_if:method,refund|nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'refund_proof.required_if' => 'Bukti transfer wajib diunggah untuk pengembalian dana.',
            'refund_proof.image' => 'Bukti transfer harus berupa gambar.',
            'refund_proof.mimes' => 'Format bukti transfer harus JPG, PNG, atau WEBP.',
            'refund_proof.max' => 'Ukuran bukti transfer maksimal 2 MB.',
        ]);

        $note = $data['note'] ?? null;

        if ($data['method'] === 'replace') {
            $replacement = DB::transaction(function () use ($retur, $creditNote, $note) {
                $replacement = $this->createReplacementTransaction($retur, $creditNote, $note);

                $this->invoiceService->markCreditNoteReplaced($creditNote, $replacement->transaction_number, $note);

                $retur->update([
                    'status' => 'Selesai',
                    'completed_at' => now(),
                ]);

                $this->recordTransactionHistory(
                    $retur->transaction,
                    "Nota kredit {$creditNote->credit_note_number} untuk retur {$retur->retur_number} diganti dengan pengiriman ulang barang lewat transaksi {$replacement->transaction_number}."
                );

                return $replacement;
            });

            return back()->with('success', "Transaksi pengganti {$replacement->transaction_number} dibuat dan siap dijadwalkan pengirimannya.");
        }

        $proofPath = $request->file('refund_proof')->store('refund_proofs', 'public');

        try {
            DB::transaction(function () use ($retur, $creditNote, $note, $proofPath) {
                $this->invoiceService->markCreditNoteRefunded($creditNote, $note, $proofPath);

                $retur->update([
                    'status' => 'Selesai',
                    'completed_at' => now(),
                ]);

                $retur->transaction->histories()->create([
                    'status' => $retur->transaction->status,
                    'description' => "Nota kredit {$creditNote->credit_note_number} untuk retur {$retur->retur_number} ditandai sudah dikembalikan.",
                    'photo_path' => $proofPath,
                    'user_id' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($proofPath);

            throw $e;
        }

        return back()->with('success', "Nota kredit {$creditNote->credit_note_number} ditandai sudah dikembalikan.");
    }

    private function createReplacementTransaction(Retur $retur, CreditNote $creditNote, ?string $note): Transaction
    {
        $retur->loadMissing('items', 'transaction');
        $parent = $retur->transaction;

        $sequence = Transaction::where('transaction_number', 'like', "{$parent->transaction_number}-R%")
            ->lockForUpdate()
            ->count() + 1;

        $replacement = Transaction::create([
            'customer_id' => $retur->customer_id,
            'credit_note_id' => $creditNote->id,
            'transaction_number' => "{$parent->transaction_number}-R{$sequence}",
            'total_price' => (int) $retur->items->sum('price'),
            'status' => 'Disetujui',
            'note' => "Pengiriman ulang retur {$retur->retur_number} (nota kredit {$creditNote->credit_note_number}) dari transaksi {$parent->transaction_number}.",
            'admin_note' => $note,
            'for_date' => now(),
            'requested_at' => now(),
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        foreach ($retur->items as $item) {
            $replacement->items()->create([
                'waste_id' => $item->waste_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'note_item' => "Pengganti retur {$retur->retur_number}",
            ]);
        }

        $replacement->histories()->create([
            'status' => 'Disetujui',
            'description' => "Transaksi pengganti dibuat dari nota kredit {$creditNote->credit_note_number} untuk retur {$retur->retur_number} atas transaksi {$parent->transaction_number}.",
            'user_id' => Auth::id(),
        ]);

        return $replacement;
    }

    public function reject(Request $request, Retur $retur)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        abort_unless($retur->status === 'Menunggu', 403, 'Retur ini sudah diproses.');

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $retur->update([
            'status' => 'Ditolak',
            'admin_note' => $data['admin_note'] ?? null,
            'rejected_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $description = "Retur {$retur->retur_number} ditolak oleh admin.";
        $description .= $retur->admin_note ? " Alasan: {$retur->admin_note}" : '';

        $this->recordTransactionHistory($retur->transaction, $description);

        return back()->with('success', "Retur {$retur->retur_number} berhasil ditolak.");
    }

    public function cancel(Retur $retur)
    {
        abort_unless(Auth::user()->hasRole('pelanggan'), 403);

        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        abort_unless($retur->customer_id === $customer->id, 403);

        abort_unless($retur->status === 'Menunggu', 403, 'Retur yang sudah diproses tidak bisa dibatalkan.');

        $retur->update([
            'status' => 'Dibatalkan',
            'cancelled_at' => now(),
        ]);

        $this->recordTransactionHistory($retur->transaction, "Retur {$retur->retur_number} dibatalkan oleh pelanggan.");

        return back()->with('success', "Retur {$retur->retur_number} berhasil dibatalkan.");
    }

    public function export(Request $request)
    {
        $returs = Retur::query()
            ->with('customer', 'transaction', 'items.waste', 'creditNote')
            ->when(!Auth::user()->hasRole('admin'), function ($query) {
                $query->whereHas('customer', function ($c) {
                    $c->where('user_id', Auth::id());
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('retur_number', 'like', "%{$request->search}%")
                        ->orWhereHas('transaction', function ($t) use ($request) {
                            $t->where('transaction_number', 'like', "%{$request->search}%");
                        })
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
            ->latest()
            ->get();

        if ($returs->isEmpty()) {
            return back()->with('error', 'Tidak ada data retur yang bisa diexport untuk filter yang dipilih.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Retur');

        $headers = ['No', 'Nomor Retur', 'Nomor Transaksi', 'Pelanggan', 'Tanggal Diajukan', 'Kode', 'Nama Limbah', 'Qty', 'Nilai', 'Status', 'Nota Kredit', 'Catatan'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $row = 2;
        foreach ($returs as $index => $retur) {
            foreach ($retur->items as $item) {
                $sheet->fromArray([
                    $index + 1,
                    $retur->retur_number,
                    $retur->transaction->transaction_number,
                    $retur->customer->customer_name,
                    optional($retur->requested_at)->translatedFormat('d F Y'),
                    $item->waste->code,
                    $item->waste->waste_name,
                    $item->qty,
                    $item->price,
                    $retur->status,
                    $retur->creditNote?->credit_note_number,
                    $item->note_item ?? $retur->note,
                ], null, "A{$row}");
                $row++;
            }
        }

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'riwayat-retur-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function recordTransactionHistory(Transaction $transaction, string $description): void
    {
        $transaction->histories()->create([
            'status' => $transaction->status,
            'description' => $description,
            'user_id' => Auth::id(),
        ]);
    }

    private function itemValue(TransactionItem $transactionItem, int $qty): int
    {
        if ($transactionItem->qty <= 0) {
            return 0;
        }

        return (int) round($transactionItem->price / $transactionItem->qty * $qty);
    }

    private function generateReturNumber(Customer $customer): string
    {
        $prefix = 'RT-' . $this->customerCode($customer) . '-' . now()->format('Ymd') . '-';

        $lastNumber = Retur::where('retur_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('retur_number')
            ->value('retur_number');

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
