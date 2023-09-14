<?php

namespace App\Models;

use App\Models\User;
use DateTimeInterface;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at'  => 'date:m-d-Y h:i A',
        'updated_at' => 'date:m-d-Y h:i A',
    ];

    protected $fillable = ['sender_id',
     'send_to_id',
     'messages_count',
      'updated_at',
    ];

    protected $hidden = ['messages_count'];


    public function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

/*     public function users()
{
    return $this->belongsToMany(User::class, 'conversations', 'id', 'sender_id');
    return $this->belongsToMany(User::class, 'conversations', 'id', 'send_to_id');

} */
public function latestMessage()
{
    return $this->hasOne(Message::class)->latest();
}

public function attachments(){
    return $this->hasMany(MessageAttachment::class);
}

}
