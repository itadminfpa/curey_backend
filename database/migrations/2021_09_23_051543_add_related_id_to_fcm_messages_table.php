<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelatedIdToFcmMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    //for notificaitons to send reservation id
    public function up()
    {
        Schema::table('fcm_messages', function (Blueprint $table) {
            $table->integer('redirection_id')->after('action_by_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fcm_messages', function (Blueprint $table) {
            $table->removeColumn('redirection_id');

        });
    }
}
