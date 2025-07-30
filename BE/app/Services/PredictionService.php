<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PredictionService
{
    private $apiUrl;
    private $timeout;
    private $csvPath;

    public function __construct()
    {
        $this->apiUrl = config('prediction.api_url');
        $this->timeout = config('prediction.timeout');
        $this->csvPath = storage_path('app/public/dataperminggutes.csv');
    }

    /**
     * Get prediction for single product
     */
    public function getPrediction($productName, $currentStock, $minStock)
    {
        $cacheKey = "prediction_{$productName}_{$currentStock}_{$minStock}";

        return Cache::remember($cacheKey, 300, function () use ($productName, $currentStock, $minStock) {
            return $this->fetchPrediction($productName, $currentStock, $minStock);
        });
    }

    /**
     * Get batch predictions for multiple products
     */
    public function getBatchPredictions($products)
    {
        try {
            $response = Http::timeout($this->timeout + 30)->post("{$this->apiUrl}/predict-multiple", [
                'products' => $products,
                'csv_path' => $this->csvPath
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success']) {
                    return $data['data'];
                }
            }

            Log::warning("Batch prediction API failed: " . $response->status());

        } catch (\Exception $e) {
            Log::error("Batch prediction exception: " . $e->getMessage());
        }

        // Fallback to individual predictions
        $results = [];
        foreach ($products as $product) {
            $results[] = $this->getPrediction(
                $product['product_name_type'],
                $product['current_stock'],
                $product['min_stock']
            );
        }

        return $results;
    }

    /**
     * Fetch prediction from API
     */
    private function fetchPrediction($productName, $currentStock, $minStock)
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->apiUrl}/predict-stock", [
                'product_name_type' => $productName,
                'csv_path' => $this->csvPath,
                'current_stock' => $currentStock,
                'min_stock' => $minStock
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success']) {
                    return $data['data'];
                } else {
                    Log::warning("Prediction API error for {$productName}: " . $data['message']);
                }
            } else {
                Log::error("Prediction API request failed for {$productName}: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::error("Exception in fetchPrediction for {$productName}: " . $e->getMessage());
        }

        return $this->getDefaultPrediction($productName, $currentStock, $minStock);
    }

    /**
     * Default prediction when API fails
     */
    private function getDefaultPrediction($productName, $currentStock, $minStock)
    {
        $weeklyAvg = max(1, $minStock * 0.5);
        $totalPredicted = $weeklyAvg * 4;
        $stockNeeded = max(0, $totalPredicted - $currentStock + $minStock);

        return [
            'product_name_type' => $productName,
            'current_stock' => $currentStock,
            'min_stock' => $minStock,
            'predicted_sales' => [
                'weekly_forecast' => [$weeklyAvg, $weeklyAvg, $weeklyAvg, $weeklyAvg],
                'total_4_weeks' => $totalPredicted
            ],
            'recommendation' => [
                'stock_to_add' => $stockNeeded,
                'suggested_order' => round($stockNeeded * 1.2),
                'priority' => $currentStock <= $minStock ? 'HIGH' : 'MEDIUM'
            ],
            'forecast_accuracy' => [
                'model_aic' => null,
                'model_bic' => null
            ],
            'note' => 'Prediction API unavailable - using default calculation'
        ];
    }

    /**
     * Check if prediction API is healthy
     */
    public function checkApiHealth()
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/health");

            if ($response->successful()) {
                $data = $response->json();
                return $data['success'] ?? false;
            }

        } catch (\Exception $e) {
            Log::warning("Prediction API health check failed: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Clear prediction cache
     */
    public function clearCache($productName = null)
    {
        if ($productName) {
            Cache::forget("prediction_{$productName}*");
        } else {
            Cache::flush();
        }
    }
}
