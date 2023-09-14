<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallTimeToEmergReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emerg_reservations', function (Blueprint $table) {
            $table->string('call_time')->after('reservation_date')->default(null)->change();
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
            $table->dropColumn('call_time');
        });
    }
}
