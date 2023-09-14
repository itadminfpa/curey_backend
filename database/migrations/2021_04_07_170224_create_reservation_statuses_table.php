<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status_title');
            $table->string('status_title_ar');
            $table->timestamps();
        });

        DB::table('reservation_statuses')->insert(
            [
                ['status_title' => 'Sent','status_title_ar' => 'تم الارسال'],
                ['status_title' => 'Viewed','status_title_ar' => 'تمت المشاهدة'],
                ['status_title' => 'Accepted','status_title_ar' => 'تمت الموافقة'],
                ['status_title' => 'Rejected','status_title_ar' => 'تم الرفض'],

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
        Schema::dropIfExists('reservation_statuses');
    }
}
