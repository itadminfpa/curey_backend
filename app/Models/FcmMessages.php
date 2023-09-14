<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FcmMessages extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'body', 'user_id', 'from', 'notification_type_id', 'action_by_id', 'redirection_id', 'is_seen'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getFromAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
}
