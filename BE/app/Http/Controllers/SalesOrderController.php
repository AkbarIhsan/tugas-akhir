<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sdetails = salesOrder::with('salesOrderDetail', 'users')->get();

        return response()->json($sdetails->map(function ($sdetails) {
            return [
                'id' => $sdetails->id,
                'date' => $sdetails->date,
                'price' => $sdetails->salesOrderDetail->price,
                'qty' => $sdetails->salesOrderDetail->qty,
                'total_price' => $sdetails->salesOrderDetail->total_price,
                'username' => $sdetails->users->username ?? null,
                'branch' => $sdetails->users->branch->branch_name ?? null,
                'product_name_type' => $sdetails->salesOrderDetail->unit->productType->product_name_type ?? null,
                'product_name' => $sdetails->salesOrderDetail->unit->productType->product->product_name ?? null,
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

    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder, $id)
    {
        $sorder = SalesOrder::with('details.unit')->findOrFail($id);
        return response()->json($sorder);
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
