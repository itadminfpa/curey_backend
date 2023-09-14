<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientRequestFinishedToEmergReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emerg_reservations', function (Blueprint $table) {
            $table->string('client_request_finished')->after('is_request_finished')->default('n');
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
            $table->removeColumn('client_request_finished');
        });
    }
}
