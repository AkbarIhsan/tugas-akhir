<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::with('productType.product', 'branch')->get();

        return response()->json($units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'price' => $unit->price,
                'stock' => $unit->stock,
                'min_stock' => $unit->min_stock,
                'branch' => $unit->branch->branch_name ?? null,
                'product_name_type' => $unit->productType->product_name_type ?? null,
                'product_name' => $unit->productType->product->product_name ?? null,
            ];
        }));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_product_type' => 'required|exists:product_type,id',
            'id_branch' => 'required|exists:branch,id',
            'unit_name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_stock' => 'required|numeric',
        ]);

        $unit = Unit::create($validated);

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit, $id)
    {
        $unit = Unit::with('productType.product', 'branch')->findOrFail($id);

        return response()->json([
            'id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'price' => $unit->price,
            'stock' => $unit->stock,
            'min_stock' => $unit->min_stock,
            'branch' => $unit->branch->branch_name ?? null,
            'product_name_type' => $unit->productType->product_name_type ?? null,
            'name_product' => $unit->productType->product->name_product ?? null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'id_product_type' => 'sometimes|exists:product_type,id',
            'id_branch' => 'sometimes|exists:branch,id',
            'unit_name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|numeric',
            'min_stock' => 'sometimes|numeric',
        ]);

        $unit->update($validated);

        return response()->json($unit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(['message' => 'Unit deleted']);
    }
}
