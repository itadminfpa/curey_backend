<?php

namespace App\Models;

use App\Models\ServiceUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceDay extends Model
{
    use HasFactory;


    protected $fillable = [
        'service_user_id',
        'day_id',
    ];

    public function user_section(){
        return $this->belongsTo(ServiceUser::class);
    }
}
