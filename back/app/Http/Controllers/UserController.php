<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\UserTransformer;

class UserController extends Controller
{
    public function index(Request $request){
    	return fractal()
    		->item($request->user())
    		->transformWith(new UserTransformer)
    		->toArray();
    }
}
