<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_section_id',
        'day_id',

    ];

    public function user_section(){
        return $this->belongsTo(UserSection::class);
    }
}
