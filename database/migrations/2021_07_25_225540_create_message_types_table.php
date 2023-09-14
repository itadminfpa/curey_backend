<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_types', function (Blueprint $table) {
            $table->id();
            $table->string('message_type');
            $table->timestamps();
        });

        DB::table('message_types')->insert(
            [
                ['message_type' => 'Text'],
                ['message_type' => 'Image'],
                ['message_type' => 'Audio'],
                ['message_type' => 'PDF']

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
        Schema::dropIfExists('message_types');
    }
}
