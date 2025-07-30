<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesOrderDetail;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index()
{
    $user = Auth::user();
    $userBranchId = $user->id_branch;

    // Eager load relasi ke user dan branch
    $salesOrders = SalesOrder::with(['users.branch'])
        ->whereHas('users', function ($query) use ($userBranchId) {
            $query->where('id_branch', $userBranchId);
        })
        ->get();

    // Format hasil
    $result = $salesOrders->map(function ($order) {
        $user = $order->users;
        $username = $user->username ?? null;
        $branchName = $user->branch->branch_name ?? null;

        $total = SalesOrderDetail::where('id_sales_order', $order->id)
            ->whereDate('created_at', '>=', $order->date)
            ->sum('total_price');

        return [
            'id' => $order->id,
            'id_user' => $order->id_user,
            'username' => $username,
            'branch_name' => $branchName,
            'date' => $order->created_at,
            'total_amount' => $total,
        ];
    });

    return response()->json($result);
}


// SalesOrderController.php
public function latest()
{
    $latest = SalesOrder::orderBy('created_at', 'desc')->first();

    return response()->json($latest);
}





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function complete()
    {
        $user = auth()->user();

        $salesOrder = SalesOrder::where('id_user', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$salesOrder) {
            return response()->json(['message' => 'Tidak ada pesanan pending'], 404);
        }

        $salesOrder->status = 'completed';
        $salesOrder->save();

        return response()->json(['message' => 'Transaksi berhasil diselesaikan']);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
public function show($id)
{
    $order = SalesOrder::with(['salesOrderDetail.unit.productType.product', 'users.branch'])->findOrFail($id);

    $details = $order->salesOrderDetail->map(function ($detail) {
        return [
            'id' => $detail->id,
            'price' => $detail->price,
            'qty' => $detail->qty,
            'total_price' => $detail->total_price,
            'username' => $detail->salesOrder->users->username ?? null,
            'branch' => $detail->salesOrder->users->branch->branch_name ?? null,
            'product_name_type' => $detail->unit->productType->product_name_type ?? null,
            'product_name' => $detail->unit->productType->product->product_name ?? null,
        ];
    });

    return response()->json([
        'id' => $order->id,
        'id_user' => $order->id_user,
        'username' => $order->users->username ?? null,
        'date' => $order->created_at,
        'details' => $details, // tetap kirim sebagai `details` agar frontend tidak error
    ]);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = SalesOrder::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'sales_order deleted']);
    }
}
