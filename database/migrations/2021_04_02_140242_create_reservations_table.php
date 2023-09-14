<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('SP_id')->nullable();
            $table->unsignedBigInteger('user_section_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('reservation_date');
            $table->integer('accept_status')->default(0);
            $table->string('is_request_finished')->default('n');


            $table->foreign('SP_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_section_id')->references('id')->on('user_sections')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('reservations');
    }
}
