<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('days', function (Blueprint $table) {
            $table->id();
            $table->string('day_name');
        });

        DB::table('days')->insert(
            [
                ['day_name' => 'Saturday'],
                ['day_name' => 'Sunday'],
                ['day_name' => 'Monday'],
                ['day_name' => 'Tuesday'],
                ['day_name' => 'Wednesday'],
                ['day_name' => 'Thursday'],
                ['day_name' => 'Friday'],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('days');
    }
}
