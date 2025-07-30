<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = Auth::user();
    $userBranchId = $user->id_branch;

    // Ambil semua purchase order milik user yang satu branch
    $purchaseOrders = PurchaseOrder::with(['users.branch', 'purchaseOrderDetail'])
        ->whereHas('users', function ($query) use ($userBranchId) {
            $query->where('id_branch', $userBranchId);
        })
        ->latest()
        ->get();

    // Format hasil
    $result = $purchaseOrders->map(function ($order) {
        $user = $order->users;
        $username = $user->username ?? null;
        $branchName = $user->branch->branch_name ?? null;

        $firstVendor = $order->purchaseOrderDetail->first()->vendor ?? null;

        $total = $order->purchaseOrderDetail->sum('total_price');

        return [
            'id' => $order->id,
            'id_user' => $order->id_user,
            'username' => $username,
            'branch_name' => $branchName,
            'vendor' => $firstVendor,
            'date' => $order->created_at,
            'total_amount' => $total,
        ];
    });

    return response()->json($result);
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
        $order = PurchaseOrder::create([
            'id_user' => auth()->id(),
            'date' => null // akan diisi saat menambah detail
        ]);

        return response()->json($order, 201);
    }

    public function complete()
    {
        $user = auth()->user();

        $purchaseOrder = PurchaseOrder::where('id_user', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$purchaseOrder) {
            return response()->json(['message' => 'Tidak ada pesanan pending'], 404);
        }

        $purchaseOrder->status = 'completed';
        $purchaseOrder->save();

        return response()->json(['message' => 'Transaksi berhasil diselesaikan']);
    }
    /**
     * Display the specified resource.
     */
public function show($id)
{
    $order = PurchaseOrder::with(['details.unit.productType.product', 'user.branch'])->findOrFail($id);

    $details = $order->details->map(function ($detail) {
        return [
            'id' => $detail->id,
            'vendor' => $detail->vendor,
            'qty' => $detail->qty,
            'cost_price' => $detail->cost_price,
            'total_price' => $detail->total_price,
            'username' => $detail->purchaseOrder->user->username ?? null,
            'branch' => $detail->purchaseOrder->user->branch->branch_name ?? null,
            'product_name_type' => $detail->unit->productType->product_name_type ?? null,
            'product_name' => $detail->unit->productType->product->product_name ?? null,
        ];
    });

    return response()->json([
        'id' => $order->id,
        'id_user' => $order->id_user,
        'username' => $order->user->username ?? null,
        'date' => $order->created_at,
        'details' => $details,
    ]);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }
}
