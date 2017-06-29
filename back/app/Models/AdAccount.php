<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdAccount extends Model
{
    protected $table = 'ad_accounts';
    protected $fillable = [
        'user_id', 'ad_account_id','service'
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
