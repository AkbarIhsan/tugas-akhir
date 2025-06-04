<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlowTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('flow_type')->insert([
            [
                'name_flow' => 'Masuk',
            ],
            [
                'name_flow' => 'Keluar',
            ],
        ]);
    }
}
