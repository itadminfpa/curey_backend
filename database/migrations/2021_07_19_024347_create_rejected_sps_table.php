<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectedSpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rejected_sps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emerg_reservation_id');
            $table->foreign('emerg_reservation_id')->references('id')->on('emerg_reservations')->onDelete('cascade');
            $table->unsignedBigInteger('SP_id');
            $table->foreign('SP_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('reservation_status_id')->default(4);
            $table->foreign('reservation_status_id')->references('id')->on('reservation_statuses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rejected_sps');
    }
}
