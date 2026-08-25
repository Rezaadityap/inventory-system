<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockHistoryController extends Controller
{
    public function index()
    {
        $histories = StockHistory::with('waste', 'user')->get();

        return view('stock-history.index', compact('histories'));
    }

    public function export(Request $request)
    {
        $histories = StockHistory::query()
            ->with('waste', 'user')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('type', 'like', "%{$request->search}%")
                        ->orWhere('source', 'like', "%{$request->search}%")
                        ->orWhereHas('waste', function ($w) use ($request) {
                            $w->where('code', 'like', "%{$request->search}%")->orWhere('waste_name', 'like', "%{$request->search}%");
                        });
                });
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->date_from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Stock');

        $headers = ['No', 'Kode', 'Nama Limbah', 'Tipe', 'Sumber', 'Qty', 'Nama Pengguna', 'Tanggal'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($histories as $index => $history) {
            $sheet->fromArray([
                $index + 1,
                $history->waste->code,
                $history->waste->waste_name,
                $history->type,
                $history->source,
                $history->qty,
                $history->user->name,
                $history->created_at->translatedFormat('d F Y'),
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'riwayat-stock-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
