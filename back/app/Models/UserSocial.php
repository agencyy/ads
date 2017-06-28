<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSocial extends Model
{
	protected $table = 'users_social';
	protected $fillable = [
		'social_id', 'picture_url','service'
	];
    public function user(){
    	return $this->belongsTo(User::class);
    }
}
