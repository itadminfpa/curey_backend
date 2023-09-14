<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpenTimeAndLimitTimeAndChargeToUserSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_sections', function (Blueprint $table) {
            //
            $table->string('from')->default("00:00");
            $table->string('to')->default("00:00");
            $table->string('waiting_time_in_mins')->default("120");
            $table->string('charge')->default("0");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_sections', function (Blueprint $table) {
            //
        });
    }
}
