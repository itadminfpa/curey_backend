<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecretCodeToEmergReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emerg_reservations', function (Blueprint $table) {
            $table->string('secret_code')->nullable(true)->after('current_long');
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
            $table->dropColumn('secret_code');

        });
    }
}
