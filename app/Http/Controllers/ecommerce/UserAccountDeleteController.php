<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class UserAccountDeleteController extends Controller
{
    public function destroy(Request $request,$id){

        $user = User::findOrFail($id);
        $user->addresses()->delete();
        $user->delete();
        Auth::logout();
        return redirect()->route('user.auth.login')->with('message','Your account has been deleted');
    }
}
