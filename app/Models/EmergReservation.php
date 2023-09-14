<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergReservation extends Model
{
    use HasFactory;

    protected $hidden = [
        'current_lat',
        'current_long',
    ];






    protected $fillable = [

        'SP_id',
        'service_user_id',
        'user_id',
        'service_id',
        'reservation_date',
        'call_time',
        'reservation_status_id',
        'client_accept_status',
        'is_request_finished',
        'current_lat',
        'current_long',
        'secret_code',
        'client_request_finished',
        'code_confirmed'
    ];

    protected $appends = ['Day_in_ar'];

    public function getDayInArAttribute(){
        return Util::get_day_in_ar($this['Day']);
    }


}
