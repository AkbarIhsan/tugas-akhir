<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderDetail;
use App\Models\SalesOrder;
use App\Models\Unit;
use Illuminate\Http\Request;

class SalesOrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    $sdetailsQuery = SalesOrderDetail::with('salesOrder.users.branch', 'unit.productType.product');

    // Jika bukan role admin (misalnya id_role != 1), filter berdasarkan cabang
    if ($user->id_role !== 1) {
        $sdetailsQuery->whereHas('salesOrder.users', function ($query) use ($user) {
            $query->where('id_branch', $user->id_branch);
        });
    }

    $sdetails = $sdetailsQuery->get();

    return response()->json($sdetails->map(function ($detail) {
        return [
            'id' => $detail->id,
            'id_sales_order' => $detail->salesOrder->id ?? null,
            'price' => $detail->price,
            'qty' => $detail->qty,
            'total_price' => $detail->total_price,
            'username' => $detail->salesOrder->users->username ?? null,
            'branch' => $detail->salesOrder->users->branch->branch_name ?? null,
            'product_name_type' => $detail->unit->productType->product_name_type ?? null,
            'product_name' => $detail->unit->productType->product->product_name ?? null,
        ];
    }));
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
        ]);

        $unit = Unit::findOrFail($validated['id_unit']);

        if ($unit->stock < $validated['qty']) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Sisa stok: ' . $unit->stock
            ], 422);
        }

        // Cari transaksi aktif (pending) dari user
        $salesOrder = SalesOrder::where('id_user', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Jika tidak ada, buat transaksi baru
        if (!$salesOrder) {
            $salesOrder = SalesOrder::create([
                'id_user' => auth()->id(),
                'date' => now(),
                'status' => 'pending',
            ]);
        }

        // Tambahkan item ke detail
        $validated['id_sales_order'] = $salesOrder->id;
        $validated['price'] = $unit->price;
        $validated['total_price'] = $validated['qty'] * $validated['price'];

        $detail = SalesOrderDetail::create($validated);

        // Kurangi stok unit
        $unit->decrement('stock', $validated['qty']);

        return response()->json($detail, 201);
    }

    public function show($id)
{
    $detail = SalesOrderDetail::with('salesOrder.users.branch', 'unit.productType.product')->findOrFail($id);

    return response()->json([
        'id' => $detail->id,
        'id_sales_order' => $detail->salesOrder->id ?? null,
        'price' => $detail->price,
        'qty' => $detail->qty,
        'total_price' => $detail->total_price,
        'username' => $detail->salesOrder->users->username ?? null,
        'branch' => $detail->salesOrder->users->branch->branch_name ?? null,
        'product_name_type' => $detail->unit->productType->product_name_type ?? null,
        'product_name' => $detail->unit->productType->product->product_name ?? null,
        'created_at' => $detail->created_at,
        'updated_at' => $detail->updated_at,
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $detail = SalesOrderDetail::findOrFail($id);
        $unit = Unit::findOrFail($detail->id_unit);

        // Kembalikan stok jika dihapus
        $unit->increment('stock', $detail->qty);

        $detail->delete();

        return response()->json(['message' => 'Detail pesanan berhasil dihapus']);
    }
}
