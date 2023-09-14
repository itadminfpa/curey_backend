<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'SP_id', 'user_rating'];
    protected $hidden = ['created_at', 'updated_at'];

    public function SP(){
        return $this->belongsTo(User::class,'SP_id');
    }
}
