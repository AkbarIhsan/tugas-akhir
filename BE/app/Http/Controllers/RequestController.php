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
        $requests = RequestModel::with([
        'transferStock',
        'transferStock.unit',
        'transferStock.units',
        'transferStock.users',     // user yang meminta
        'transferStock.user2'     // user yang diminta
    ])->orderBy('created_at', 'desc')->get();

    return response()->json([
        'message' => 'Data request berhasil diambil.',
        'data' => $requests,
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
    public function show(RequestModel $requestModel)
    {
        //
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
        $requestData = $request->validate([
            'status' => 'required|in:success,rejected',
        ]);

        $req = RequestModel::with('transferStock')->findOrFail($id);
        $transfer = $req->transferStock;

        // Cek apakah user login adalah penerima request
        if (auth()->id() !== $transfer->id_user_2) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk mengubah status request ini.'
            ], 403);
        }

        // Jika status sudah success atau rejected sebelumnya, jangan izinkan update lagi
        if (in_array($req->status, ['success', 'rejected'])) {
            return response()->json([
                'message' => 'Status request sudah final dan tidak bisa diubah.'
            ], 422);
        }

        // Update status
        $req->update(['status' => $requestData['status']]);

        if ($requestData['status'] === 'success') {
            $qty = $transfer->qty_product_request;

            // Ambil branch dari user pengirim & penerima
            $branch_user_2 = \App\Models\User::findOrFail($transfer->id_user_2)->id_branch;
            $branch_user_1 = \App\Models\User::findOrFail($transfer->id_user)->id_branch;

            // Kurangi stok dari unit pengirim
            $unitFrom = Unit::where('id', $transfer->id_unit_gives)
                ->where('id_branch', $branch_user_2)
                ->firstOrFail();

            if ($unitFrom->stock < $qty) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi pada unit pengirim.',
                ], 422);
            }

            $unitFrom->decrement('stock', $qty);

            // Tambahkan stok ke unit penerima
            $unitTo = Unit::where('id', $transfer->id_unit_request)
                ->where('id_branch', $branch_user_1)
                ->firstOrFail();

            $unitTo->increment('stock', $qty);
        }

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            'data' => $req->fresh('transferStock'),
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
