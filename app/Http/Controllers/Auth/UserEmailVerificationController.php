<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class UserEmailVerificationController extends Controller
{
    public function verify($id, $hash){

        $user = User::findOrFail($id);
        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('user.login')->with('message', 'Email already verified.');
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        return redirect()->route('user.login')->with('message', 'Email successfully verified.');
     }
}
