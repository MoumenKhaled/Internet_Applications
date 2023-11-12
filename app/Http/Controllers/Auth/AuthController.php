<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\User;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function register (Request $request){
        $validator=$request->validate([
            'user_name'=>'required',
            'email'=>'required|email',     
            'password'=>'required|confirmed'
        ]);
        $user = $this->userService->registerUser($validator);      
        return response($user);
    }
    
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);      
        $user = $this->userService->loginUser($data);
        return $user;
    }
}
