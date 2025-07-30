<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesOrderDetail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExportHarianKeExcel extends Command
{
    protected $signature = 'transaksi:export-harian';
    protected $description = 'Export transaksi harian ke file Excel';

    public function handle(): int
    {
        $tanggal = Carbon::today()->toDateString();

        // Ambil data transaksi hari ini
    $dataHariIni = SalesOrderDetail::whereDate('sales_order_detail.created_at', $tanggal)
        ->join('unit', 'sales_order_detail.id_unit', '=', 'unit.id')
        ->join('product_type', 'unit.id_product_type', '=', 'product_type.id')
        ->select('product_type.product_name_type', DB::raw('SUM(sales_order_detail.qty) as total'))
        ->groupBy('product_type.product_name_type')
        ->pluck('total', 'product_type.product_name_type')
        ->toArray();

        $path = storage_path('app/public/dataTransaksi.xlsx');

        if (!file_exists($path)) {
            $this->error("❌ File Excel tidak ditemukan di: $path");
            return 1;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $headers = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1')[0];
        $newRow = [$tanggal];

        foreach (array_slice($headers, 1) as $namaProduk) {
            $jumlah = $dataHariIni[$namaProduk] ?? 0;
            $newRow[] = $jumlah;
        }

        $lastRow = $sheet->getHighestRow() + 1;
        $sheet->fromArray($newRow, null, "A{$lastRow}");

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("✅ Data transaksi tanggal $tanggal berhasil disimpan ke Excel.");
        return 0;
    }
}
