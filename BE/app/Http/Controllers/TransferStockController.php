<?php

namespace App\Http\Controllers;

use App\Models\TransferStock;
// use App\Models\RequestModel;
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
    $user = auth()->user();

    // Ambil semua transfer stock yang melibatkan user ini
    $allTransfers = TransferStock::with([
        'user:id,username,id_branch',
        'user.branch:id,branch_name',
        'user2:id,username,id_branch',
        'user2.branch:id,branch_name',
        'unit_request:id,unit_name,id_product_type',
        'unit_request.productType:id,product_name_type',
        'unit_gives:id,unit_name,id_product_type',
        'unit_gives.productType:id,product_name_type',
    ])
    ->where(function ($query) use ($user) {
        $query->where('id_user', $user->id)
        ->orWhere('id_user_2', $user->id);
    })
    ->orderByDesc('created_at')
    ->get();

    // Pisahkan berdasarkan jenis keterlibatan user
    $myRequests = $allTransfers->where('id_user', $user->id)->values();
    $incomingRequests = $allTransfers->where('id_user_2', $user->id)->values();

    return response()->json([
        'message' => 'Data transfer stock ditemukan.',
        'data' => [
            'my_requests' => $myRequests,
            'incoming_requests' => $incomingRequests,
        ]
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
    $user = auth()->user(); // Pengaju request

    $validated = $request->validate([
        'id_branch' => 'required|exists:branch,id', // Cabang tujuan
        'id_unit_request' => 'required|exists:unit,id',
        'qty_product_request' => 'required|numeric|min:1',
    ]);

    // ❗ Dilarang request ke cabangnya sendiri
    if ((int) $validated['id_branch'] === (int) $user->id_branch) {
        return response()->json([
            'message' => 'Anda tidak dapat membuat permintaan ke cabang Anda sendiri.',
        ], 403);
    }

    // Ambil unit request
    $unitRequest = Unit::findOrFail($validated['id_unit_request']);

    // ❗ Validasi bahwa unit yang diminta berasal dari cabang user
    if ((int) $unitRequest->id_branch !== (int) $user->id_branch) {
        return response()->json([
            'message' => 'Unit yang diminta harus berasal dari cabang Anda.',
        ], 403);
    }

    // Cari unit dari cabang tujuan yang punya jenis produk sama
    $unitGives = Unit::where('id_branch', $validated['id_branch'])
        ->where('id_product_type', $unitRequest->id_product_type)
        ->first();

    if (!$unitGives) {
        return response()->json([
            'message' => 'Tidak ditemukan unit pemberi di cabang tujuan dengan jenis produk yang sama.',
        ], 404);
    }

    // Cari owner dari cabang tujuan
    $ownerTujuan = User::where('id_branch', $validated['id_branch'])
        ->where('id_role', 2)
        ->first();

    if (!$ownerTujuan) {
        return response()->json([
            'message' => 'Owner cabang tujuan tidak ditemukan.',
        ], 404);
    }

    // Simpan transfer stock
    $transferStock = TransferStock::create([
        'id_user' => $user->id,
        'id_user_2' => $ownerTujuan->id,
        'id_unit_request' => $unitRequest->id,
        'id_unit_gives' => $unitGives->id,
        'qty_product_request' => $validated['qty_product_request'],
        'status' => 'pending',
    ]);


    return response()->json([
        'message' => 'Transfer stock berhasil dibuat.',
        'data' => $transferStock,
    ], 201);
}





    /**
     * Display the specified resource.
     */
public function show($id)
{
    $user = auth()->user();

    $transfer = TransferStock::with([
        'user:id,username',
        'user2:id,username',
        'unit_request:id,unit_name,id_product_type',
        'unit_request.productType:id,product_name_type',
        'unit_gives:id,unit_name,id_product_type',
        'unit_gives.productType:id,product_name_type',
    ])->findOrFail($id);

    // Hanya user tujuan (id_user_2) yang boleh melihat
    if ($transfer->id_user_2 !== $user->id) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses ke transfer stock ini.'
        ], 403);
    }

    return response()->json([
        'message' => 'Detail transfer stock ditemukan.',
        'data' => $transfer
    ]);
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
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,success,rejected',
    ]);

    $transfer = TransferStock::with(['unit_gives', 'unit_request'])->findOrFail($id);

    // Cek apakah user yang update adalah pemilik cabang tujuan
    if ($transfer->id_user_2 !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Jika status yang baru adalah 'success' dan sebelumnya bukan 'success'
    if ($validated['status'] === 'success' && $transfer->status !== 'success') {
        // Kurangi stok dari unit pemberi
        $unitGives = $transfer->unit_gives;
        $unitGives->stock -= $transfer->qty_product_request;
        $unitGives->save();

        // Tambah stok ke unit permintaan
        $unitRequest = $transfer->unit_request;
        $unitRequest->stock += $transfer->qty_product_request;
        $unitRequest->save();
    }

    $transfer->update([
        'status' => $validated['status'],
    ]);

    return response()->json([
        'message' => 'Status transfer stock berhasil diperbarui.',
        'data' => $transfer
    ]);
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
    // RequestModel::where('id_transfer_stock', $transferStock->id)->delete();

    // Hapus transfer stock
    $transferStock->delete();

    return response()->json([
        'message' => 'Transfer stock dan permintaan terkait berhasil dihapus.',
    ]);
    }
}
