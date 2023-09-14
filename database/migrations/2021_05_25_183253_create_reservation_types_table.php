<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->timestamps();
        });
        DB::table('reservation_types')->insert(
            [
                ['type' => 'normal'],
                ['type' => 'oncall']

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
        Schema::dropIfExists('reservation_types');
    }
}
