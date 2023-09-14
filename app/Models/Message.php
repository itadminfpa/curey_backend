<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\MessageType;
use App\Models\Conversation;
use App\Models\MessageAttachment;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPUnit\Framework\MockObject\ClassIsFinalException;

class Message extends Model
{
    use HasFactory;


    protected $casts = [
        'created_at'  => 'date:m-d-Y h:i A',
        'updated_at' => 'date:m-d-Y h:i A',
    ];



    protected $hidden = ['updated_at'];


    protected $fillable = [
        'user_id',// User that created this message
        'conversation_id',
        'body', // Body of the message
        'is_deleted',
        'sent_at',
        'message_type_id'
    ];

    //override the serializeDate method

    public function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getSentAtAttribute($value){
        $value = $this->created_at;
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments(){
        return $this->hasMany(MessageAttachment::class);
    }

    public function message_type(){
        return $this->belongsTo(MessageType::class);
    }

    public static function AddToTypeFolder($input){
        switch ($input){
            case 2:
                $type = 'images';
                break;
            case 3:
                $type = 'Audio';
                break;
            case 4:
                $type = 'Files';
                break;
        }
        return $type;
    }
}
