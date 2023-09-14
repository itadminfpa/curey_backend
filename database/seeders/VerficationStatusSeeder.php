<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VerficationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('verification_status')->insert(
            [
                ['status' => 'Approved','status_ar' => 'مقبول'],
                ['status' => 'Pending','status_ar' => 'مٌعلق'],
                ['status' => 'Rejected','status_ar' => 'مرفوض'],

            ]
        );
    }
}
