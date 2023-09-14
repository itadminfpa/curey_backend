<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RejectionReason extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'rejection_reason_id',
        'comment'

    ];

    public function reservation(){
        return $this->belongsTo(Reservation::class);
    }

}
