<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EmergReservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_title',
        'service_title_ar',
        'icon_id'
    ];

    protected $hidden = ['created_at',
                         'updated_at'];


    public static function get_sps_ids($reservation, $nearby_users ){

        $sps = EmergReservation::where(['emerg_reservations.id' => $reservation->id,'service_user.service_id' => $reservation->service_id])
        ->whereIn('service_user.user_id', $nearby_users)
        ->whereRaw('TIME_FORMAT(emerg_reservations.call_time, "%H:%i") between TIME_FORMAT(service_user.from, "%H:%i") and TIME_FORMAT(service_user.to, "%H:%i")')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->join('service_user','services.id','=','service_user.service_id')
        ->join('service_days','service_days.service_user_id', '=', 'service_user.id')
        ->join('days','service_days.day_id', '=', 'days.id')
        ->where('days.day_name', Carbon::now()->format('l'))
        ->distinct()
        ->pluck('service_user.user_id')->toArray();

        return $sps;
    }


    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
