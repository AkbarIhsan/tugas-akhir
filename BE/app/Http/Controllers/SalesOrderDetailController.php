<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderDetail;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sdetails = salesOrderDetail::with('salesOrder', 'unit')->get();

        return response()->json($sdetails->map(function ($sdetails) {
            return [
                'id' => $sdetails->id,
                'price' => $sdetails->price,
                'qty' => $sdetails->qty,
                'total_price' => $sdetails->total_price,
                'username' => $sdetails->salesOrder->users->username ?? null,
                'branch' => $sdetails->salesOrder->users->branch->branch_name ?? null,
                'product_name_type' => $sdetails->unit->productType->product_name_type ?? null,
                'product_name' => $sdetails->unit->productType->product->product_name ?? null,
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
            'id_unit' => 'required|exists:unit,id',
            'qty' => 'required|integer|min:1',
        ]);

        $unit = \App\Models\Unit::findOrFail($validated['id_unit']);

        if ($unit->stock < $validated['qty']) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Sisa stok: ' . $unit->stock
            ], 422);
        }

        $salesOrder = SalesOrder::create([
            'id_user' => auth()->id(),
            'date' => now(),
        ]);

        $validated['id_sales_order'] = $salesOrder->id;
        $validated['price'] = $unit->price;
        $validated['total_price'] = $validated['qty'] * $validated['price'];

        $detail = SalesOrderDetail::create($validated);

        $unit->decrement('stock', $validated['qty']);

        return response()->json($detail, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrderDetail $salesOrderDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrderDetail $salesOrderDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrderDetail $salesOrderDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $detail = SalesOrderDetail::findOrFail($id);
        $unit = \App\Models\Unit::findOrFail($detail->id_unit);

        $unit->increment('stock', $detail->qty);
        $detail->delete();

        return response()->json(['message' => 'Detail pesanan berhasil dihapus']);
    }
}
