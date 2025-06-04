<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\Unit;

class PurchaseOrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $details = PurchaseOrderDetail::with('purchaseOrder', 'unit')->get();

        return response()->json($details->map(function ($details) {
            return [
                'id' => $details->id,
                'vendor' => $details->vendor,
                'price' => $details->price,
                'qty' => $details->qty,
                'total_price' => $details->total_price,
                'username' => $details->purchaseOrder->users->username ?? null,
                'branch' => $details->purchaseOrder->users->branch->branch_name ?? null,
                'product_name_type' => $details->unit->productType->product_name_type ?? null,
                'product_name' => $details->unit->productType->product->product_name ?? null,
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
            'vendor' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        // Buat purchase_order terlebih dahulu
        $purchaseOrder = PurchaseOrder::create([
            'id_user' => auth()->id(),
            'date' => now(),
        ]);

        // Tambahkan id_purchase_order yang baru saja dibuat ke validated data
        $validated['id_purchase_order'] = $purchaseOrder->id;

        // Hitung total harga
        $validated['total_price'] = $validated['qty'] * $validated['price'];

        // Cari unit dan update stock dan price
        $unit = Unit::findOrFail($validated['id_unit']);
        $unit->increment('stock', $validated['qty']);
        $unit->price = $validated['price'];
        $unit->save();

        // Simpan detail purchase order
        $detail = PurchaseOrderDetail::create($validated);

        return response()->json($detail, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrderDetail $purchaseOrderDetail, $id)
    {
        $detail = PurchaseOrderDetail::with(['purchaseOrder.user', 'unit'])->findOrFail($id);

        return response()->json([
            'id' => $detail->id,
            'purchase_order' => [
                'id' => $detail->purchaseOrder->id,
                'date' => $detail->purchaseOrder->date,
                'user' => $detail->purchaseOrder->user->name ?? null,
            ],
            'unit' => [
                'id' => $detail->unit->id,
                'unit_name' => $detail->unit->unit_name,
            ],
            'vendor' => $detail->vendor,
            'qty' => $detail->qty,
            'price' => $detail->price,
            'total_price' => $detail->total_price,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrderDetail $purchaseOrderDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrderDetail $purchaseOrderDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $detail = PurchaseOrderDetail::findOrFail($id);
        $unit = Unit::findOrFail($detail->id_unit);

        $unit->decrement('stock', $detail->qty);
        $detail->delete();

        return response()->json(['message' => 'Detail pembelian dihapus']);
    }
}
