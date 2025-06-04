<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branch')->insert([
            [
                'branch_name' => 'Toko Utama',
                'branch_address' => 'Jl. Mojoroto, Kediri',
            ],
                        [
                'branch_name' => 'Toko Cabang 2',
                'branch_address' => 'Jl. Lirboyo, Kediri',
            ],
                        [
                'branch_name' => 'Toko Cabang 3',
                'branch_address' => 'Jl. Muning, Kediri',
            ],
        ]);
    }
}
