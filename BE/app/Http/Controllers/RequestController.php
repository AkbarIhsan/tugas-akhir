<?php

namespace App\Http\Controllers;

use App\Models\RequestModel;
use App\Models\Unit;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    $requests = RequestModel::with([
        'transferStock.user:id,username',
        'transferStock.user2:id,username',
        'transferStock.unit_request:id,unit_name,id_product_type',
        'transferStock.unit_request.productType:id,product_name_type',
        'transferStock.unit_gives:id,unit_name,id_product_type,stock',
        'transferStock.unit_gives.productType:id,product_name_type',
    ])
    ->whereHas('transferStock', function ($query) use ($user) {
        $query->where('id_user', $user->id)
            ->orWhere('id_user_2', $user->id);
    })
    ->get();

    return response()->json([
        'message' => 'Daftar request yang terkait dengan Anda.',
        'data' => $requests
    ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
public function show($id)
{
    $user = auth()->user();

    $request = RequestModel::with([
        'transferStock.user:id,username',
        'transferStock.user2:id,username',
        'transferStock.unit_request:id,unit_name,id_product_type',
        'transferStock.unit_request.productType:id,product_name_type',
        'transferStock.unit_gives:id,unit_name,id_product_type,stock',
        'transferStock.unit_gives.productType:id,product_name_type',
    ])->findOrFail($id);

    // Cek apakah user adalah pengirim atau penerima
    if (
        $request->transferStock->id_user !== $user->id &&
        $request->transferStock->id_user_2 !== $user->id
    ) {
        return response()->json([
            'message' => 'Kamu tidak memiliki akses untuk melihat request ini.'
        ], 403);
    }

    return response()->json([
        'message' => 'Detail request berhasil diambil.',
        'data' => $request
    ]);
}




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestModel $requestModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    $user = auth()->user();

    $validated = $request->validate([
        'status' => 'required|in:success,rejected',
    ]);

    $requestStock = RequestModel::with('transferStock')->findOrFail($id);
    $transfer = $requestStock->transferStock;

    // Validasi hanya user penerima (id_user_2) yang boleh update
    if ($transfer->id_user_2 !== $user->id) {
        return response()->json([
            'message' => 'Kamu tidak memiliki akses untuk mengubah status request ini.'
        ], 403);
    }

    // Jika status sudah final, tidak boleh diubah lagi
    if (in_array($requestStock->status, ['success', 'rejected'])) {
        return response()->json([
            'message' => 'Status request sudah final dan tidak bisa diubah.'
        ], 422);
    }

    // Update status dulu
    $requestStock->update(['status' => $validated['status']]);

    if ($validated['status'] === 'success') {
        $qty = $transfer->qty_product_request;

        // Ambil unit pengirim (dari transfer_stock)
        $unitFrom = Unit::findOrFail($transfer->id_unit_gives);

        if ($unitFrom->stock < $qty) {
            return response()->json([
                'message' => 'Stok tidak mencukupi pada unit pengirim.',
            ], 422);
        }

        // Kurangi stok dari pengirim
        $unitFrom->decrement('stock', $qty);

        // Tambah stok ke unit penerima
        $unitTo = Unit::findOrFail($transfer->id_unit_request);
        $unitTo->increment('stock', $qty);
    }

    return response()->json([
        'message' => 'Status berhasil diperbarui.',
        'data' => $requestStock->fresh('transferStock'),
    ]);
}




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestModel $requestModel)
    {
        //
    }
}
