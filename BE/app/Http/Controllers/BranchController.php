<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the branches.
     */
    public function index()
    {
        $branches = Branch::all();

        return response()->json([
            'message' => 'Daftar cabang berhasil diambil.',
            'data' => $branches
        ]);
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'required|string|max:255',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'Cabang berhasil ditambahkan.',
            'data' => $branch
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch)
    {
        return response()->json([
            'message' => 'Detail cabang berhasil diambil.',
            'data' => $branch
        ]);
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'required|string|max:255',
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'Cabang berhasil diperbarui.',
            'data' => $branch
        ]);
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json([
            'message' => 'Cabang berhasil dihapus.'
        ]);
    }
}
