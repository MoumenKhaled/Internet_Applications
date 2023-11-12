<?php
// app/Services/UserService.php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserService
{
    public function registerUser($userData)
    {
        $email=User::where('email', $userData['email'])->first();
        $username=User::where('user_name', $userData['user_name'])->first();
        if ($email){
               $user='This email has already been taken';
        }
        else if ($username){
            $user='This username has already been taken';
        }
        else {
        // Create a new user in the database
        $user = new User();
        $user->user_name = $userData['user_name'];
        $user->email =  $userData['email'];
        $user->password = Hash::make($userData['password']); // تشفير كلمة المرور
        $user->save();
        // You can perform additional business logic here if needed
        $token = $user->createToken('apiToken')->plainTextToken;
        $user=[
            'user' => $user,
            'token' => $token 
        ];
        }
        return $user ;
    }
    public function loginUser($userData)
    {
        $user = User::where('email', $userData['email'])->first();
        if ($user && Hash::check($userData['password'], $user->password)) {
            $token = $user->createToken('apiToken')->plainTextToken;
            $user = [
                'user' => $user,
                'token' => $token
             ];
        }
        else if (!$user) $user="this account doesn't exist";
        else   $user= "invaild password";

        return $user;
    }
}