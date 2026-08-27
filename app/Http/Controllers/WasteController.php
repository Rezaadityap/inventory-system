<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Waste;
use Illuminate\Http\Request;

class WasteController extends Controller
{
    public function index()
    {
        $wastes = Waste::with('category')->paginate(10);
        $categories = Category::all();

        return view('waste.index', compact('wastes', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:wastes,code',
            'waste_name' => 'required|string',
            'unit' => 'required|in:Gram,Miligram,Kilogram',
            'unit_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        Waste::create($data);

        return redirect()->route('wastes.index')->with('success', 'Limbah berhasil ditambahkan.');
    }

    public function update(Request $request, Waste $waste)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:wastes,code,' . $waste->id,
            'waste_name' => 'required|string',
            'unit' => 'required|in:Gram,Miligram,Kilogram',
            'unit_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $waste->update($data);

        return redirect()->route('wastes.index')->with('success', 'Limbah berhasil diedit.');
    }

    public function destroy(Waste $waste)
    {
        $waste->delete();

        return redirect()->route('wastes.index')->with('success', 'Limbah berhasil dihapus.');
    }
}
