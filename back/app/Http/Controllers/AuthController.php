<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdAccounts;
use JWTAuth;
use Carbon\Carbon;
use Socialite;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Facebook;
class AuthController extends Controller
{
    public function signup(Request $request){
    	$this->validate($request, [
    		'name' =>'required',
    		'email'=> 'required | email | unique:users',
    		'password'=> 'required'
    	]);

    	User::create([
    		'name' => $request->input('name'),
    		'email'=> $request->input('email'),
    		'password'=>bcrypt($request->input('password'))
    	]);
    }

    public function signin(Request $request){

    	$credentials = $request->only('email', 'password');
    	$expire		 = ['exp'=> Carbon::now()->addWeek()->timestamp];
    	
    	try {
    		if (!$token = JWTAuth::attempt($credentials,$expire)){
    			return response()->json([
    					'error' => 'invalid-creds'
    			], 401);
    		}
    	} catch (JWTException $e)  {
    		return response()->json([
    			'error' => 'Could not create token'
    		],500);
    	}

    	return response()->json(compact('token'));
    }

    public function redirect($service){
        if($service == 'facebook'){
            $redirect_url = Socialite::with($service)->fields([
                'first_name', 'last_name', 'email', 'gender', 'birthday', 'adaccounts', 'businesses'])
            ->scopes([
                'email', 'user_birthday', 'ads_management', 'business_management'])
            ->stateless()->redirect()->getTargetUrl(); //this allows the transaction to be stateless which will make it work as an api
        }else{
            $redirect_url = Socialite::with($service)->stateless()->redirect()->getTargetUrl();
        }
    
        return response()->json(['redirect_url'=>$redirect_url]);
    }

    public function callback($service, Request $request){
        $flow = '';
        $long_token = NULL;
        $error = $request->input('error');
        if($error == 'access_denied'){
            $flow = $error;
             return redirect()->to(config('app.client_url') . '?' . 'flow='.$flow);#redirect to angular 
        }

        $flow = "login";
        if($service == 'facebook'){
            $serviceUser = Socialite::driver($service)->stateless()->fields(['name','first_name','last_name','email',
                    'gender','verified','birthday', 'posts','adaccounts', 'businesses'])->user();
            $token = $serviceUser->token;
            $long_token = Facebook::getLongFacebookToken($token);
        }else{
            $serviceUser = Socialite::driver($service)->user();
        }
        $user = $this->getExistingUser($serviceUser, $service);
        if(!$user){
            $flow = "registered";
            if($service = 'facebook'){
                $user = User::create([
                    'email'=> $serviceUser->getEmail(),
                    'name' => $serviceUser->getName(),
                    'first_name' => $serviceUser->user['first_name'],
                    'last_name' => $serviceUser->user['last_name'],
                    'picture_url' => $serviceUser->avatar,
                    'gender' => $serviceUser->user['gender'],
                    'active' => true,
                    'slug' => str_random(10)
                ]);
            }else{
                $user = User::create([
                    'email'=> $serviceUser->getEmail(),
                    'name' => $serviceUser->getName(),
                    'picture_url' => $serviceUser->avatar,
                    'active' => true,
                    'slug' => str_random(10),
                ]);
            }
        }

        if ($this->needsToCreateSocial($user, $service)){
            $user->social()->create([
                'social_id' => $serviceUser->getId(),
                'service' => $service,
                'picture_url' => $serviceUser->avatar
            ]);
        }

        $user->social()->first()->update(['access_token'=>$long_token]);

        //ad accounts
        $ad_accounts = Facebook::getAdAccountsArray($serviceUser);
        foreach($ad_accounts as $ad_account){
            $account_id = $ad_account['account_id'];
            if ($this->needsToCreateAdAccount($user, $account_id)){
                $user->adAccount()->create([
                    'ad_account_id' => $account_id,
                    'service'=>$service,
                ]);
            }
        }

        //Bussiness Accounts
        $businesses = Facebook::getBusinessesArray($serviceUser);
        foreach($businesses as $business){
            $name = $business['name'];
            $business_id = $business['id'];
            if ($this->needsToCreateBusiness($user, $account_id)){
                $user->business()->create([
                    'business_id' => $business_id,
                    'name'=>$name,
                    'service'=>$service,
                ]);
            }
        }

        $jwt_token = JWTAuth::fromUser($user);//gets the JWT token from the user model json web token for authenticating on the front end

        return redirect()->to(config('app.client_url') . '?' . 'token=' . $jwt_token . '&flow='.$flow);#redirect to angular client side
        // return response()->json(['flow'=>$flow, 'token'=>$token, 'URL'=> config('app.client_url')]);
    }

    protected function needsToCreateSocial($user, $service){
        return !$user->hasSocialLinked($service);
    }

    protected function needsToCreateAdAccount($user, $ad_account_id){
        return !$user->hasAdAccountLinked($ad_account_id);
    }

    protected function needsToCreateBusiness($user, $business_id){
        return !$user->hasBusinessLinked($business_id);
    }

    public function getExistingUser($serviceUser, $service){
        return User::where('email', $serviceUser->getEmail())->orWhereHas('social', function($q) use ($serviceUser, $service){
            $q->where('social_id', $serviceUser->getId())->where('service', $service);
        })->first();
    }
}
