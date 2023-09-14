<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sp_types')->insert(
            [
                ['type' => 'type 1','type_ar' => 'النوع 1'],
                ['type' => 'type 2','type_ar' => 'النوع 2'],
                ['type' => 'type 3','type_ar' => 'النوع 3'],
                ['type' => 'type 4','type_ar' => 'النوع 4'],
                ['type' => 'type 5','type_ar' => 'النوع 5'],
                ['type' => 'type 6','type_ar' => 'النوع 6'],
                ['type' => 'type 7','type_ar' => 'النوع 7'],
                ['type' => 'type 8','type_ar' => 'النوع 8'],
                ['type' => 'type 9','type_ar' => 'النوع 9'],
                ['type' => 'type 10','type_ar' => 'النوع 10'],

            ]
        );
    }
}
