<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    public function index()
    {
        $wastes = Waste::orderBy('waste_name')->get();
        $categories = Category::orderBy('category_name')->get();

        return view('stock.index', compact('wastes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_id' => 'required|exists:wastes,id',
            'type' => 'required|in:Masuk,Keluar',
            'remark' => 'nullable|string',
            'qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $stock = Stock::firstOrCreate(
                ['waste_id' => $validated['waste_id']],
                ['qty' => 0]
            );

            $stock = Stock::where('waste_id', $validated['waste_id'])
                ->lockForUpdate()
                ->first();

            $qtyBefore = $stock->qty;

            if ($validated['type'] === 'Masuk') {
                $qtyAfter = $qtyBefore + $validated['qty'];
            } else {
                $qtyAfter = $qtyBefore - $validated['qty'];

                if ($qtyAfter < 0) {
                    throw ValidationException::withMessages([
                        'qty' => 'Stok tidak mencukupi. Sisa stok saat ini: ' . $qtyBefore,
                    ]);
                }
            }

            $stock->update(['qty' => $qtyAfter]);

            StockHistory::create([
                'waste_id' => $validated['waste_id'],
                'type' => $validated['type'],
                'source' => 'Manual',
                'qty' => $validated['qty'],
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'remark' => $validated['remark'],
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('stocks.index')->with('success', 'Stock berhasil dibuat.');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();

        return redirect()->route('stocks.index')->with('success', 'Stock berhasil dihapus.');
    }
}
