<?php

namespace App\Models;

use App\Models\Message;
use App\Models\Section;
use App\Models\Service;
use App\Models\FcmTokens;
use App\Models\FcmMessages;
use App\Models\Conversation;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'lat',
        'long',
        'role_id',
        'address',
        'district_id',
        'gender',
        'description',
        'verification_status_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function SpNumbers()
    {
        return $this->hasMany(SpNumber::class, 'user_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'user_sections');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }


    public function ratings(){
       return $this->hasMany(Rating::class, 'SP_id');
    }

    public function getRateAttribute($value)
    {
        return number_format(round($value,1), 1);
    }

    public function fcm_tokens(){
        return $this->hasMany(FcmTokens::class);
    }

    public function fcm_messages(){
        return $this->hasMany(FcmMessages::class);
    }


    //array of userFCMtokens
    public function getTokens(){
        return $this->fcm_tokens()->pluck('fcm_token')->toArray();
    }

    public function license(){
        return $this->hasOne(SpLlicense::class, 'SP_id');
    }

    public function tickets(){
        return $this->hasMany(Ticket::class);
    }


}
