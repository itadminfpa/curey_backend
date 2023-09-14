<?php

namespace App\Models;

use App\Models\ServiceDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceUser extends Model
{
    use HasFactory;
    public $table = 'service_user';

    protected $fillable = [
        'service_id',
        'user_id',
        'from',
        'to',
        'waiting_time_in_mins',
        'charge',
    ];


    public function section_days(){
        return $this->HasMany(ServiceDay::class);
    }

}
