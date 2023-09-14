<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmergReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emerg_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('SP_id')->nullable();
            $table->foreign('SP_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('service_user_id')->nullable();
            $table->foreign('service_user_id')->references('id')->on('service_user')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->date('reservation_date');
            $table->unsignedBigInteger('reservation_status_id')->default(null);
            $table->foreign('reservation_status_id')->references('id')->on('reservation_statuses')->onDelete('cascade');
            $table->integer('client_accept_status')->default(0);
            $table->string('is_request_finished')->default('n');
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
        Schema::dropIfExists('emerg_reservations');
    }
}
