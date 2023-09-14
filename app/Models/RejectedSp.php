<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedSp extends Model
{
    use HasFactory;

    protected $fillable = [

        'emerg_reservation_id',
        'SP_id',
        'reservation_status_id'

    ];
}
