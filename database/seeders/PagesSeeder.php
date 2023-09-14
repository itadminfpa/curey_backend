<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert(
            [
                ['title' => 'About Us','body' => 'this is About Us page'],
                ['title' => 'Privacy Policy','body' => 'this is Privacy Policy page'],
                ['title' => 'Terms Of Us','body' => 'this is Terms Of Us page'],
                ['title' => 'Patients Privacy Policy','body' => 'this is Patients Privacy Policy page'],
            ]
        );
    }
}
