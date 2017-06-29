<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use JWTAuth;
use Carbon\Carbon;
use Socialite;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

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
        $redirect_url = Socialite::with($service)->stateless()->redirect()->getTargetUrl(); //this allows the transaction to be stateless which will make it work as an api
        return response()->json(['redirect_url'=>$redirect_url]);
    }

    public function callback($service, Request $request){
        $flow = '';
        $error = $request->input('error');
        if($error == 'access_denied'){
            $flow = $error;
             return redirect()->to(config('app.client_url') . '?' . 'flow='.$flow);#redirect to angular 
        }

        $flow = "login";
        if($service == 'facebook'){
            $serviceUser = Socialite::driver($service)->stateless()->fields(['name','first_name','last_name','email',
                    'gender','verified','birthday', 'posts'])->user();
        }else{
            $serviceUser = Socialite::driver($service)->user();
        }
        $token = $serviceUser->token;
        $url = config('services.facebook.graph_url').'/oauth/access_token?grant_type=fb_exchange_token&client_id='.config('services.facebook.client_id').'&client_secret='.config('services.facebook.client_secret').'&fb_exchange_token='.$token;
        $client = new Client();
        $result = $client->get($url);

        dd($url);
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
                    'slug' => str_random(10),
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
                'picture_url' => $serviceUser->avatar,
            ]);
        }

        $token = JWTAuth::fromUser($user);//gets the JWT token from the user model

        return redirect()->to(config('app.client_url') . '?' . 'token=' . $token . '&flow='.$flow);#redirect to angular client side
        // return response()->json(['flow'=>$flow, 'token'=>$token, 'URL'=> config('app.client_url')]);
    }

    protected function needsToCreateSocial($user, $service){
        return !$user->hasSocialLinked($service);
    }

    public function getExistingUser($serviceUser, $service){
        return User::where('email', $serviceUser->getEmail())->orWhereHas('social', function($q) use ($serviceUser, $service){
            $q->where('social_id', $serviceUser->getId())->where('service', $service);
        })->first();
    }
}
