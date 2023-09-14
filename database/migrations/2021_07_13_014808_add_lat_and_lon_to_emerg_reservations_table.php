<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatAndLonToEmergReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emerg_reservations', function (Blueprint $table) {
            $table->string('current_lat')->after('is_request_finished');
            $table->string('current_long')->after('current_lat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emerg_reservations', function (Blueprint $table) {
            $table->dropColumn('current_lat');
            $table->dropColumn('current_long');
        });
    }
}
