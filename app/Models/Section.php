<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_title',
        'is_verified',
        'icon_id',
        'section_title_ar',
        'section_description',


    ];

    public function user_sections(){
        return $this->hasMany(UserSection::class);
    }

    public function section_title(){
        return $this->section_title;
    }

    public function users(){
        return $this->belongsToMany(User::class, 'user_sections');
    }


}
