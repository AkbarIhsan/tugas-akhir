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
        $details = PurchaseOrderDetail::with('purchaseOrder', 'unit')->latest()->get();

        return response()->json($details->map(function ($details) {
            return [
                'id' => $details->id,
                'vendor' => $details->vendor,
                'cost_price' => $details->cost_price,
                'qty' => $details->qty,
                'created_at' => $details->created_at,
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
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $validated = $request->validate([
        'id_unit' => 'required|exists:unit,id',
        'qty' => 'required|integer|min:1',
        'vendor' => 'required|string|max:255',
        'cost_price' => 'required|numeric|min:0',
    ]);

    $unit = Unit::findOrFail($validated['id_unit']);

    // Cari transaksi aktif (pending) dari user
    $purchaseOrder = PurchaseOrder::where('id_user', $user->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    // Jika tidak ada, buat transaksi baru
    if (!$purchaseOrder) {
        $purchaseOrder = PurchaseOrder::create([
            'id_user' => $user->id,
            'date' => now(),
            'status' => 'pending',
        ]);
    }

    // Tambahkan id_purchase_order ke validated data
    $validated['id_purchase_order'] = $purchaseOrder->id;

    // Hitung total harga
    $validated['total_price'] = $validated['qty'] * $validated['cost_price'];

    // Tambahkan detail ke purchase_order
    $detail = PurchaseOrderDetail::create($validated);

    // Tambahkan stok unit dan perbarui cost_price
    $unit->increment('stock', $validated['qty']);
    $unit->cost_price = $validated['cost_price'];
    $unit->save();

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
            'cost_price' => $detail->cost_price,
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
