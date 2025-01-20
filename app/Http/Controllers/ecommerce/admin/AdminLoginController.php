<?php

namespace App\Http\Controllers\ecommerce\admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function login()
    {
        return view('ecommerce.admin.login');
    }

    public function auth(Request $request){
        // $request->authenticate();

        $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        $input = $request->all();
        $data = [
            'email' => $input['email'],
            'password' => $input['password'],
        ];

        if(Auth::attempt(['email' => $data['email'], 'password' => $data['password']]) && Auth::user()->role_id == 1)
        {
            return redirect()->route('admin.index');
        }
        else{
            return redirect()->route('admin.login')->with('error','wrong credentials');
        }
    }

}
