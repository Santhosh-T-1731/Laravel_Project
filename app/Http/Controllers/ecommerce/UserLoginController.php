<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Session;
use Throwable;
use Validator;

class UserLoginController extends Controller
{
    public function login(){
        return view('ecommerce.user.user-login');
    }

    public function register(){
        return view('ecommerce.user.user-register');
    }

    public function authRegister(Request $request){
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'dob' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            $user = User::create(attributes: [
                'name' => $request->name,
                'email' => $request->email,
                'dob'=>$request->dob,
                'password' => Hash::make($request->password),
            ]);

            Mail::to($user->email)->send(new VerifyEmail($user));
            Auth::logout();
            DB::commit();
            return redirect()->route('user.login')->with('message','Registration successful. Please check your email to verify your account.');
        }
        catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function authLogin(Request $request){


        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $data = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if((Auth::attempt(['email'=>$data['email'], 'password' => $data['password']])) && Auth::user()->role_id != 1)
        {

            return redirect()->route('user.index');
        }
        else{
            Auth::logout();
            return back()->with('error','Wrong Credentials');
        }
    }

}
