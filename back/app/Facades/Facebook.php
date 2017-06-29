<?php

namespace App\Facades;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class Facebook {
	public static function getLongFacebookToken($short_token){ //this converts the short lived facebook token gotten from login to a long lived token
        $url = config('services.facebook.graph_url').'/oauth/access_token?grant_type=fb_exchange_token&client_id='.config('services.facebook.client_id').'&client_secret='.config('services.facebook.client_secret').'&fb_exchange_token='.$short_token;

        $client = new Client();
        $res = $client->request('GET', $url);
        $body = json_decode($res->getBody());
        $access_token = $body->access_token;
        return $access_token;
    }

    public static function getAdAccountsArray($service_user){
    	$accounts = $service_user->user['adaccounts']['data'];
    	return $accounts;
    }

    public static function getBusinessesArray($service_user){
        $accounts = $service_user->user['businesses']['data'];
        return $accounts;
    }
}