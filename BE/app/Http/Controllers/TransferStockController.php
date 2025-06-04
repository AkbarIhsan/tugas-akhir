<?php

namespace App\Http\Controllers;

use App\Models\TransferStock;
use App\Models\RequestModel;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;

class TransferStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'id_user_2' => 'required|exists:users,id',
            'id_unit_request' => 'required|exists:unit,id',
            'id_unit_gives' => 'required|exists:unit,id',
            'qty_product_request' => 'required|numeric',
        ]);

        // Ambil user yang diminta
        $user2 = User::findOrFail($validated['id_user_2']);

        // Ambil unit pengirim berdasarkan id_branch dari user2
        $unitFrom = Unit::where('id', $validated['id_unit_gives'])
            ->where('id_branch', $user2->id_branch)
            ->firstOrFail();

        $productPriceGives = $unitFrom->price;
        $totalPrice = $productPriceGives * $validated['qty_product_request'];


        // Buat data transfer stock
        $transferStock = TransferStock::create([
            'id_user' => auth()->id(),
            'id_user_2' => $validated['id_user_2'],
            'id_unit_request' => $validated['id_unit_request'],
            'id_unit_gives' => $validated['id_unit_gives'],
            'product_price_gives' => $productPriceGives,
            'qty_product_request' => $validated['qty_product_request'],
            'total_price' => $totalPrice,
        ]);

        // Buat request terkait
        RequestModel::create([
            'id_transfer_stock' => $transferStock->id,
            'status' => 'pending',
            'date' => now(),
        ]);

        return response()->json([
            'message' => 'Transfer stock berhasil dibuat.',
            'data' => $transferStock,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TransferStock $transferStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransferStock $transferStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransferStock $transferStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
            // Cek apakah transfer stock ditemukan
    $transferStock = TransferStock::findOrFail($id);

    // Optional: batasi hanya user yang membuat transfer stock bisa menghapus
    if ($transferStock->id_user !== auth()->id()) {
        return response()->json([
            'message' => 'Kamu tidak memiliki izin untuk menghapus transfer stock ini.'
        ], 403);
    }

    // Hapus request terkait
    RequestModel::where('id_transfer_stock', $transferStock->id)->delete();

    // Hapus transfer stock
    $transferStock->delete();

    return response()->json([
        'message' => 'Transfer stock dan permintaan terkait berhasil dihapus.',
    ]);
    }
}
