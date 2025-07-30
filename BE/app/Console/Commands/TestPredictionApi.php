<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PredictionService;

class TestPredictionApi extends Command
{
    protected $signature = 'prediction:test {product?}';
    protected $description = 'Test prediction API functionality';

    public function handle()
    {
        $predictionService = app(PredictionService::class);

        // Health check
        $this->info('🔍 Checking API health...');
        $isHealthy = $predictionService->checkApiHealth();

        if ($isHealthy) {
            $this->info('✅ Prediction API is healthy');
        } else {
            $this->error('❌ Prediction API is not responding');
            return 1;
        }

        // Test prediction
        $productName = $this->argument('product') ?: 'Cat Biru';
        $this->info("🔮 Testing prediction for: {$productName}");

        $prediction = $predictionService->getPrediction($productName, 5, 10);

        if ($prediction) {
            $this->info('✅ Prediction successful');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Product', $prediction['product_name_type']],
                    ['Current Stock', $prediction['current_stock']],
                    ['Min Stock', $prediction['min_stock']],
                    ['4-Week Forecast', $prediction['predicted_sales']['total_4_weeks'] ?? 'N/A'],
                    ['Stock to Add', $prediction['recommendation']['stock_to_add'] ?? 'N/A'],
                    ['Suggested Order', $prediction['recommendation']['suggested_order'] ?? 'N/A'],
                    ['Priority', $prediction['recommendation']['priority'] ?? 'N/A'],
                ]
            );

            if (isset($prediction['note'])) {
                $this->warn('⚠️  ' . $prediction['note']);
            }
        } else {
            $this->error('❌ Prediction failed');
            return 1;
        }

        return 0;
    }
}
