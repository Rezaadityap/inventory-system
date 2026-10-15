<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index()
    {
        return view('invoice.index');
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        abort_unless($user->hasRole('pelanggan'), 403);
        abort_unless($invoice->customer?->user_id === $user->id, 403, 'Invoice ini bukan milik Anda.');

        if (! $invoice->isPayable()) {
            $message = $invoice->isOverdue()
                ? 'Invoice ini sudah lewat jatuh tempo dan tidak bisa dibayar lagi.'
                : 'Invoice ini sudah tidak bisa dibayar.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        try {
            $invoice = $this->invoiceService->startPayment($invoice);
        } catch (\Throwable $e) {
            report($e);

            $message = 'Gagal membuka pembayaran. Coba lagi beberapa saat.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            throw ValidationException::withMessages(['invoice' => $message]);
        }

        if ($request->wantsJson()) {
            return response()->json(['invoice' => $this->snapPayload($invoice)]);
        }

        return back()->with('success', 'Silakan selesaikan pembayaran pada jendela yang terbuka.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('transaction.items.waste', 'transaction.approver', 'customer');

        $user = Auth::user();

        abort_unless(
            $user->hasRole('admin') || $invoice->customer?->user_id === $user->id,
            403,
        );

        abort_unless($invoice->status === 'paid', 404, 'Kwitansi hanya tersedia untuk invoice yang sudah lunas.');

        $logoPath = public_path('ssg.png');
        $logoBase64 = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'logoBase64'))->setPaper('a4', 'landscape');

        $safeNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);

        return $pdf->stream("Invoice-{$safeNumber}.pdf");
    }

    public function export(Request $request)
    {
        $invoices = Invoice::query()
            ->with('customer', 'transaction')
            ->when(!Auth::user()->hasRole('admin'), function ($query) {
                $query->whereHas('customer', function ($c) {
                    $c->where('user_id', Auth::id());
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('invoice_number', 'like', "%{$request->search}%")
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
            ->when($request->month, function ($query) use ($request) {
                $query->whereMonth('created_at', $request->month);
            })
            ->when($request->year, function ($query) use ($request) {
                $query->whereYear('created_at', $request->year);
            })
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'Tidak ada data invoice yang bisa diexport untuk filter yang dipilih.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Invoice');

        $headers = ['No', 'Nomor Invoice', 'Nomor Transaksi', 'Pelanggan', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status', 'Jatuh Tempo', 'Tanggal Bayar', 'Tanggal Dibuat'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $row = 2;
        foreach ($invoices as $index => $invoice) {
            $sheet->fromArray([
                $index + 1,
                $invoice->invoice_number,
                $invoice->transaction?->transaction_number,
                $invoice->customer?->customer_name,
                (int) $invoice->subtotal,
                (int) $invoice->discount,
                (int) $invoice->tax,
                (int) $invoice->total,
                $invoice->statusLabel,
                optional($invoice->due_date)->translatedFormat('d F Y'),
                optional($invoice->paid_at)->translatedFormat('d F Y H:i'),
                $invoice->created_at->translatedFormat('d F Y'),
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'daftar-invoice-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function snapPayload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'snap_token' => $invoice->snap_token,
            'expired_at' => optional($invoice->expired_at)->translatedFormat('d M Y H:i'),
        ];
    }
}
