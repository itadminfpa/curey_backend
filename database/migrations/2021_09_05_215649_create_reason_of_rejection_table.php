<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReasonOfRejectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reasons_of_rejection', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->string('reason_ar');
            $table->timestamps();
        });
        Artisan::call('db:seed', [
            '--class' => 'RejectionReasonsSeeder',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reasons_of_rejection');
    }
}
