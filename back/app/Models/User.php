<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password', 'picture_url', 'birthday', 'gender', 'slug'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function social(){
        return $this->hasMany(UserSocial::class);
    }

    public function adAccount(){
        return $this->hasMany(AdAccount::class);
    }

    public function business(){
        return $this->hasMany(Business::class);
    }

    public function hasSocialLinked($service){
        return (bool) $this->social->where('service', $service)->count();
    }

    public function hasAdAccountLinked($ad_account_id){
        return (bool) $this->adAccount->where('ad_account_id', $ad_account_id)->count();
    }

    public function hasBusinessLinked($business_id){
        return (bool) $this->business->where('business_id', $business_id)->count();
    }
}
