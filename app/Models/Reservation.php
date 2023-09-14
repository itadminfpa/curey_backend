<?php

namespace App\Models;

use App\Helpers\Util;
use App\Models\RejectionReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;


    protected $fillable = [
        'SP_id',
        'user_section_id',
        'user_id',
        'reservation_date',
        'start_time',
        'end_time',
        'is_request_finished',
        'reservation_status_id',
        'secret_code',
        'section_id',
        'reservation_type_id',
        'client_request_finished',
        'code_confirmed',
        'is_rated'


    ];

    protected $hidden = ['created_at',
                         'updated_at'];

    protected $appends = ['Day_in_ar'];

    public function getDayInArAttribute(){
        return Util::get_day_in_ar($this['Day']);
    }

    public function reason()
    {
        return $this->hasOne(RejectionReason::class)->select('reasons_of_rejection.reason', 'comment')
        ->leftjoin('reasons_of_rejection','reasons_of_rejection.id','=','rejection_reasons.rejection_reason_id');
    }

}
