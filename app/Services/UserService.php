<?php
// app/Services/UserService.php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Mail\RegisterUserMail;
use Illuminate\Support\Facades\Mail;

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
        $user->verification_code = rand(100000, 999999);
        $user->save();
        //send email to verifiy

       // Send verification email
        Mail::to($user->email)->send(new RegisterUserMail($user,$user->verification_code));
        // You can perform additional business logic here if needed
        $token = $user->createToken('apiToken')->plainTextToken;
        $user=[
            'user' => $user,
            'token' => $token
        ];
        }
        return $user ;
    }
    public function activate_email_service($userData)
    {
        $user = User::where('verification_code', $userData['code'])->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            $response='The account has been activated successfully';
        } else {
                $response= 'Error in the entered code, please try again';
        }
        return $response;
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
        else if (!$user) $user="This account doesn't exist";
        else   $user= "The password is incorrect";

        return $user;
    }
}
