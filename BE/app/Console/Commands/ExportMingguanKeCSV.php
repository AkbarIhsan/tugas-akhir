<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesOrderDetail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportMingguanKeCSV extends Command
{
    protected $signature = 'transaksi:export-mingguan';
    protected $description = 'Export data transaksi mingguan ke CSV';

public function handle(): void
    {
        $this->info('📦 Mengekspor data transaksi mingguan...');

        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d'); // Senin
        $endOfWeek = Carbon::now()->endOfWeek();

        // List produk yang ingin jadi kolom header
        $productColumns = [
            'Besi Pipa 6m', 'Besi Siku 6m', 'Besi Kotak 30x30 6m', 'Besi Kotak 40x40 6m', 'Plat Strip Besi 6m',
            'Pakan Las', 'Besi Ulir 10mm', 'Cincin Besi 6x10', 'Cincin Besi 7x11', 'Cincin besi 8x12',
            'Besi H 6m', 'Cat Kuning', 'Cat Merah', 'Cat Abu-abu', 'Cat Putih', 'Cat Biru'
        ];

        // Ambil data mingguan, grup berdasarkan nama produk, hitung total quantity
        $dataMingguan = SalesOrderDetail::with('unit.productType')
            ->whereBetween('created_at', [Carbon::parse($startOfWeek), $endOfWeek])
            ->get()
            ->groupBy(fn($item) => $item->unit->productType->product_name_type ?? 'Unknown')
            ->map(fn($items) => $items->sum('qty'));

        $filename = storage_path('app/public/dataperminggutes.csv');

        // Load data lama jika ada, dengan format: key = /minggu (week_start), value = row data produk (associative)
        $existingData = [];
        if (file_exists($filename)) {
            $rows = array_map('str_getcsv', file($filename));
            $headerRow = array_shift($rows);

            foreach ($rows as $row) {
                $week = $row[0];
                $existingData[$week] = [];

                // Map setiap produk sesuai header, mulai dari index 1 (karena 0 adalah /minggu)
                foreach ($productColumns as $index => $productName) {
                    // Jika kolom produk ada di file CSV, ambil nilai, kalau tidak isi 0
                    $existingData[$week][$productName] = isset($row[$index + 1]) ? (int)$row[$index + 1] : 0;
                }
            }
        }

        // Update atau tambah data minggu ini
        // Buat array produk default dengan 0 quantity
        $newWeekData = array_fill_keys($productColumns, 0);

        // Isi data minggu ini dengan quantity yang ada dari query
        foreach ($dataMingguan as $productName => $totalQty) {
            if (in_array($productName, $productColumns)) {
                $newWeekData[$productName] = $totalQty;
            } else {
                // Kalau produk baru (tidak ada di kolom), bisa diabaikan atau ditambahkan ke unknown (optional)
            }
        }

        // Simpan ke existingData (overwrite minggu ini)
        $existingData[$startOfWeek] = $newWeekData;

        // Tulis ulang CSV
        $csv = fopen($filename, 'w');

        // Tulis header (kolom)
        fputcsv($csv, array_merge(['/minggu'], $productColumns), ",");

        // Tulis setiap baris data mingguan
        foreach ($existingData as $week => $products) {
            $row = [$week];
            foreach ($productColumns as $productName) {
                $row[] = $products[$productName] ?? 0;
            }
            fputcsv($csv, $row, ",");
        }

        fclose($csv);

        $this->info("✅ Data mingguan berhasil ditambahkan atau diperbarui ke: storage/app/dataperminggu.csv");
    }
}
