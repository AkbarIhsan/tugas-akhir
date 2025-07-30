<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prediction API Configuration
    |--------------------------------------------------------------------------
    */
    'api_url' => env('PREDICTION_API_URL', 'http://localhost:5000'),
    'timeout' => env('PREDICTION_API_TIMEOUT', 30),
    'csv_path' => env('CSV_DATA_PATH', 'storage/app/public/dataperminggutes.csv'),

    /*
    |--------------------------------------------------------------------------
    | ARIMA Model Parameters
    |--------------------------------------------------------------------------
    */
    'arima' => [
        'order' => [1, 1, 1], // (p, d, q)
        'forecast_periods' => 4, // weeks
        'safety_buffer' => 0.1, // 10% additional buffer
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety Stock Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'high_priority' => 0, // stock <= min_stock
        'medium_priority' => 0.2, // stock <= min_stock * 1.2
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Requirements
    |--------------------------------------------------------------------------
    */
    'min_data_points' => 5,
    'auto_refresh_interval' => 300, // 5 minutes in seconds
];
