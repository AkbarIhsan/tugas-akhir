<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductType;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductType::with('product')->get();
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
            'id_product' => 'required|exists:product,id',
            'product_name_type' => 'required|string|max:255',
        ]);

        $productType = ProductType::create($validated);
        return response()->json($productType, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return ProductType::with('product')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productType = ProductType::findOrFail($id);

        $validated = $request->validate([
            'id_product' => 'sometimes|exists:product,id',
            'product_name_type' => 'sometimes|string|max:255',
        ]);

        $productType->update($validated);
        return response()->json($productType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productType = ProductType::findOrFail($id);
        $productType->delete();

        return response()->json(['message' => 'Product type deleted']);
    }
}
