<?php

namespace App\Models;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'message_id' ,'created_at', 'updated_at'];

    protected $fillable = [
        'message_id',
        'attachment_path'
    ];


    public function message(){
        return $this->belongsTo(Message::class);
    }

    public function attachments(){
        return $this->hasManyThrough(Conversation::class, Message::class);
    }
}
