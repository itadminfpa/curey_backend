<?php

namespace App\Models;

use App\Models\TicketReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{

    use HasFactory;

    protected $guarded = [];


    public function TicketReply(){
        return $this->hasOne(TicketReply::class);
    }

    public function User(){
        return $this->belongsTo(User::class)->select(['id','name','email','role_id','phone','address']);
    }

}
