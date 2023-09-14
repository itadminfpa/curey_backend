<?php

namespace App\Models;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketReply extends Model
{
    protected $table = 'ticket_replys';
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    protected $guarded = [];


    public function Ticket(){
        return $this->belongsTo(Ticket::class);
    }

}
