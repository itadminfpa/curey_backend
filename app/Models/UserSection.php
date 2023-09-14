<?php

namespace App\Models;

use App\Models\Reservation;
use App\Models\UserSection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserSection extends Model
{

    public $table = 'user_sections';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'section_id',
        'from',
        'to',
        'waiting_time_in_mins',
        'charge',
        'is_emergency',
    ];

    public function section(){
        return $this->belongsTo(Section::class);
    }
    public function section_days(){
        return $this->HasMany(SectionDay::class);
    }

    public function section_name(){
//        $user_section=UserSection::find($this->id);
        return $this->id;
    }

    public function store_days(Array $days){
        SectionDay::where('user_section_id',$this->id)->delete();
        foreach ($days as $day_id) {
            SectionDay::create(
                ['user_section_id'=>$this->id,
                'day_id'=>$day_id
                ]);
        }
        return true;
    }

    public function users(){
        return $this->belongsToMany(User::class, 'user_sections','id');
    }


    public function available_hours($UserSection,$user_section_id, $date){

    $from = (int)$UserSection->from;
    $to = (int)($UserSection->to);
    $available_hours = range($from, $to, 1);
    array_pop($available_hours);

       $res_date= Reservation::where(['user_section_id' => $user_section_id,'reservation_date' => $date, 'reservation_status_id' => 3]);
        //if date doesn't exist
        if (! $res_date) {
            return response(["available hours" => json_encode($available_hours)],Response::HTTP_OK);

        }
        //if date exists
        else{
            $array_of_start_times = $res_date->pluck('start_time')->toArray();
            $casted_to_integers = array_map( function($value) { return (int)$value; }, $array_of_start_times);
            foreach ($casted_to_integers as $number){
                if(($pos = array_search($number, $available_hours)) !== false){

                    unset($available_hours[$pos]);
                }
            }
            $data = array_values($available_hours);
            return response(["available hours" => $data],Response::HTTP_OK);


    }

    }

}
