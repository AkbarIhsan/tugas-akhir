<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\PredictionService;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    $unitsQuery = Unit::with('productType.product', 'branch');

    // Jika user bukan role 1 (admin), filter berdasarkan branch user
    if ($user->id_role != 1) {
        $unitsQuery->where('id_branch', $user->id_branch);
    }

    $units = $unitsQuery->get();

    return response()->json($units->map(function ($unit) {
        $stockPercentage = ($unit->min_stock > 0 && $unit->stock >= 0)
            ? round(($unit->stock / $unit->min_stock) * 100, 2)
            : 0;

        return [
            'id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'price' => $unit->price,
            'cost_price' => $unit->cost_price,
            'stock' => $unit->stock,
            'min_stock' => $unit->min_stock,
            'stock_percentage' => $stockPercentage,
            'branch' => $unit->branch->branch_name ?? null,
            'product_name_type' => $unit->productType->product_name_type ?? null,
            'product_name' => $unit->productType->product->product_name ?? null,
            'is_low_stock' => $unit->stock <= $unit->min_stock && $unit->min_stock > 0,
            'stock_status' => $unit->stock == 0 ? 'OUT_OF_STOCK' : ($unit->stock <= $unit->min_stock && $unit->min_stock > 0 ? 'LOW' : 'NORMAL')
        ];
    }));
}

    /**
     * Get safety stock data with predictions
     */
    public function getSafetyStock()
    {
        \Log::info('Safety stock endpoint hit!');

        $user = auth()->user();

        // Ambil semua unit yang stock-nya menipis
        $lowStockUnits = Unit::with('productType.product', 'branch')
            ->where('id_branch', $user->id_branch)
            ->whereRaw('stock <= min_stock')
            ->get();

        $safetyStockData = [];

        foreach ($lowStockUnits as $unit) {
            $productName = $unit->productType->product_name_type ?? null;

            if (!$productName) {
                continue;
            }

            // Panggil API prediksi
            $prediction = $this->getPrediction($productName, $unit->stock, $unit->min_stock);

            $safetyStockData[] = [
                'id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'product_name' => $productName,
                'product_name_type' => $unit->productType->product_name_type ?? null,
                'current_stock' => $unit->stock,
                'min_stock' => $unit->min_stock,
                'price' => $unit->price,
                'cost_price' => $unit->cost_price,
                'branch' => $unit->branch->branch_name ?? null,
                'prediction' => $prediction,
                'last_updated' => now()->toDateTimeString()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $safetyStockData,
            'total_low_stock_items' => count($safetyStockData),
            'message' => 'succsess'
        ]);
    }

    /**
     * Get prediction using service
     */
    private function getPrediction($productName, $currentStock, $minStock)
    {
        $predictionService = app(PredictionService::class);
        return $predictionService->getPrediction($productName, $currentStock, $minStock);
    }

    /**
     * Fallback prediction when API fails
     */
    private function getDefaultPrediction($currentStock, $minStock)
    {
        $stockNeeded = max(0, $minStock * 2 - $currentStock);

        return [
            'product_name' => 'Unknown',
            'current_stock' => $currentStock,
            'min_stock' => $minStock,
            'predicted_sales' => [
                'weekly_forecast' => [$minStock * 0.5, $minStock * 0.5, $minStock * 0.5, $minStock * 0.5],
                'total_4_weeks' => $minStock * 2
            ],
            'recommendation' => [
                'stock_to_add' => $stockNeeded,
                'suggested_order' => $stockNeeded * 1.2,
                'priority' => $currentStock <= $minStock ? 'HIGH' : 'MEDIUM'
            ],
            'forecast_accuracy' => [
                'model_aic' => null,
                'model_bic' => null
            ],
            'note' => 'Prediction unavailable - using default calculation'
        ];
    }

    /**
     * Get batch predictions for multiple products
     */
public function getBatchPredictions()
{
    $user = auth()->user();

    $lowStockUnits = Unit::with('productType.product', 'branch')
        ->where('id_branch', $user->id_branch)
        ->whereRaw('stock <= min_stock')
        ->get();

    $products = $lowStockUnits->map(function ($unit) {
        return [
            'product_name' => $unit->productType->product_name_type ?? 'Unknown',
            'current_stock' => $unit->stock,
            'min_stock' => $unit->min_stock,
        ];
    })->filter(function ($product) {
        return $product['product_name'] !== 'Unknown';
    })->values()->toArray();

    try {
        // Pakai absolute path langsung
        $csvPath = 'D:\Tugas Akhir\kasir\tugas-akhir\BE\storage\app\public\dataperminggutes.csv';

        $apiUrl = 'http://localhost:5000/predict-multiple';

        $response = Http::timeout(60)->post($apiUrl, [
            'products' => $products,
            'csv_path' => $csvPath
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if ($data['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $data['data']
                ]);
            }
        }
    } catch (\Exception $e) {
        Log::error("Batch prediction error: " . $e->getMessage());
    }

    return $this->getSafetyStock();
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_product_type' => 'required|exists:product_type,id',
            'id_branch' => 'required|exists:branch,id',
            'unit_name' => 'required|string',
            'price' => 'required|numeric',
            'cost_price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_stock' => 'required|numeric',
        ]);

        $unit = Unit::create($validated);

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $unit = Unit::with('productType.product', 'branch')->findOrFail($id);

        return response()->json([
            'id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'price' => $unit->price,
            'cost_price' => $unit->price,
            'stock' => $unit->stock,
            'min_stock' => $unit->min_stock,
            'branch' => $unit->branch->branch_name ?? null,
            'product_name_type' => $unit->productType->product_name_type ?? null,
            'name_product' => $unit->productType->product->product_name ?? null,
            'is_low_stock' => $unit->stock <= $unit->min_stock,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'id_product_type' => 'sometimes|exists:product_type,id',
            'id_branch' => 'sometimes|exists:branch,id',
            'unit_name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'cost_price' => 'sometimes|numeric',
            'stock' => 'sometimes|numeric',
            'min_stock' => 'sometimes|numeric',
        ]);

        $unit->update($validated);

        return response()->json($unit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(['message' => 'Unit deleted']);
    }
}
