<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\SalesOrder;

use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    $deliveries = Delivery::with(['salesOrder.users.branch', 'customer'])
        ->whereHas('salesOrder.users', function ($query) use ($user) {
            $query->where('id_branch', $user->id_branch);
        })
        ->get();

    $result = $deliveries->map(function ($delivery) {
        $salesOrder = $delivery->salesOrder;
        $user = $salesOrder?->users;
        $branch = $user?->branch;

        return [
            'id' => $delivery->id,
            'id_sales_order' => $delivery->id_sales_order,
            'id_customer' => $delivery->id_customer,
            'date' => $delivery->date,
            'status' => $delivery->status,
            'created_at' => $delivery->created_at,
            'updated_at' => $delivery->updated_at,
            'sales_order' => [
                'id' => $salesOrder->id ?? null,
                'id_user' => $salesOrder->id_user ?? null,
                'username' => $user->username ?? null,
                'branch_name' => $branch->branch_name ?? null,
                'date' => $salesOrder->date ?? null,
                'status' => $salesOrder->status ?? null,
                'created_at' => $salesOrder->created_at ?? null,
                'updated_at' => $salesOrder->updated_at ?? null,
            ],
            'customer' => [
                'id' => $delivery->customer->id ?? null,
                'name' => $delivery->customer->name ?? null,
                'customer_address' => $delivery->customer->customer_address ?? null,
                'phone' => $delivery->customer->phone ?? null,
                'created_at' => $delivery->customer->created_at ?? null,
                'updated_at' => $delivery->customer->updated_at ?? null,
            ]
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
        $validated = $request->validate([
            'id_sales_order' => 'required|exists:sales_order,id',
            'id_customer' => 'required|exists:customer,id',
        ]);

        $validated['date'] = now();
        $validated['status'] = 'pending';

        $delivery = Delivery::create($validated);
        return response()->json($delivery, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Delivery $delivery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delivery $delivery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    // Cari delivery berdasarkan ID
    $delivery = Delivery::findOrFail($id);

    // Cek jika status saat ini sudah final
    if ($delivery->status === 'completed') {
        return response()->json([
            'message' => 'Status sudah final dan tidak bisa diubah.'
        ], 422);
    }

    // Validasi input
    $validated = $request->validate([
        'status' => 'in:pending,completed',
    ]);

    // Update data
    $delivery->update($validated);

    return response()->json($delivery);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery)
    {
        //
    }
}
