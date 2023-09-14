<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification_types')->insert(
            [
                ['type' => 'Normal Reservation'],
                ['type' => 'Emergency Reservation'],
                ['type' => 'Messages'],

                ]
            );
    }
}
