<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        'id_sales_order' => 'required|exists:sales_order,id',
        'id_customer' => 'required|exists:customer,id',
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
