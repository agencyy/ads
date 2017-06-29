<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';
    protected $fillable = [
        'user_id', 'name','service', 'business_id',
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
