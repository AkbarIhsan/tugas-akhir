<?php

namespace App\Http\Controllers;

use App\Models\MoneyFlow;
use Illuminate\Http\Request;

class MoneyFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    $moneyFlows = MoneyFlow::with(['flowType', 'users.branch'])
        ->whereIn('id_user', function ($query) use ($user) {
            $query->select('id')
                ->from('users')
                ->where('id_branch', $user->id_branch);
        })
        ->orderBy('date', 'desc')
        ->get();

    return response()->json($moneyFlows);
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
            'id_flow_type' => 'required|exists:flow_type,id',
            'qty_money' => 'required|integer',
            'description' => 'required|string|max:255',
        ]);

        $money = MoneyFlow::create([
            'id_user' => auth()->id(),
            'id_flow_type' => $validated['id_flow_type'],
            'qty_money' => $validated['qty_money'],
            'description' => $validated['description'],
            'date' => now(),
        ]);
        return response()->json($money, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MoneyFlow $moneyFlow)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MoneyFlow $moneyFlow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MoneyFlow $moneyFlow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MoneyFlow $moneyFlow)
    {
        //
    }
}
