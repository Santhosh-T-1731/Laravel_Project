<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailForPasswordReset;
use App\Models\PasswordResetToken;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Str;
use Throwable;
use Illuminate\Support\Facades\Validator;

class ForgetPasswordController extends Controller
{
    public function showForgetPasswordForm(){
        return view('ecommerce.user.forgetPasswordForm');
    }

    public function submitForgetPasswordForm(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:users,email',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $token = Str::random(64);
        DB::beginTransaction();
        try{
            $user = PasswordResetToken::create([
                'email' => $request->email,
                'token' => $token,
            ]);
            // dd($user->email);
            Mail::to($user->email)->send(new VerifyEmailForPasswordReset($user));
            DB::commit();
            return back()->with('message','We have e-mailed your password reset link!');
        }
        catch(Throwable $e)
        {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function showResetPasswordForm($token){
        Auth::logout();
        return view('ecommerce.user.forgetPasswordLink',['token'=> $token]);
    }

    public function submitResetPasswordForm(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $updatePassword = PasswordResetToken::where([
            'email' => $request->email,
            'token' => $request->token,
        ])->first();

        if(!$updatePassword){
            return back()->withInput()->with('error', 'Invalid token!');
        }
        DB::beginTransaction();
        try{
            User::where('email',$request->email)
            ->update([
                'password' => Hash::make($request->password),
            ]);
            PasswordResetToken::where('email',$request->email)->delete();

            DB::commit();
            return redirect()->route('user.login')->with('message','Your Password has been changed!');
        }
        catch(Throwable $e)
        {
            DB::rollback();
            return $e->getMessage();
        }

    }
}
