<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RejectionReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('reasons_of_rejection')->insert(
            [
                ['reason' => 'Unforeseen circumstances', 'reason_ar' => 'ظروف طارئة'],
                ['reason' => 'No available seats', 'reason_ar' => 'لا توجد أماكن متوفرة'],
                ['reason' => 'Doctor or medical service provider is not available ', 'reason_ar' => 'الطبيب أو مقدم الخدمة الطبية غير متاح'],
                ['reason' => 'Other', 'reason_ar' => 'أخرى'],
            ]
        );
    }
}
