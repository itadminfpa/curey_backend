<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpLlicense extends Model
{
    protected $table = 'sp_licenses';
    use HasFactory;
    protected $fillable = ['SP_id', 'license', 'sp_type_id'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


    public function SP(){
        return $this->belongsTo(User::class);
    }
}
