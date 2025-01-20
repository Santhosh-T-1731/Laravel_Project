<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDescription;
use App\Models\OrderStatus;
use App\Models\Products;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use Throwable;
use Validator;

class UserController extends Controller
{
    public function index(){
        if(Auth::check()){
            $user_id = auth()->user()->getAuthIdentifier();
            $session_user = Session::get('session_user',[]);
            if(!isset(session($session_user)[$user_id])){
                $session_user[$user_id] = [
                    'id' => $user_id,
                ];
            }
        Session::put('session_user',$session_user);
        }
        $data = [];
        $data['featured_products'] = Products::where('is_featured',1)->get();
        $data['most_popular_products'] = Products::limit(30)->get();
        $data['recent_products'] = Products::orderByDesc('id')->take(30)->get();
        $productIds = OrderDescription::distinct()->pluck('product_id');
        $data['best_selling_products'] = $best = Products::whereIn('id', $productIds)->get();
        return view('ecommerce.layouts.index',$data);
    }

    public function profile(){
        $id = Auth::id();
        $user = User::where('id',$id)->first();
        $addresses = Address::where('user_id',$id)->get();
        return view('ecommerce.user.userAccount',['user'=>$user,'addresses'=>$addresses]);
    }

    public function manageAddress(){
        $id = Auth::id();
        $user = User::where('id',$id)->first();
        $addresses = Address::where('user_id',$id)->get();
        return view('ecommerce.user.user-address',['user'=>$user,'addresses'=>$addresses]);
    }

    public function profileUpdate(Request $request)
    {
        $id = Auth::id();
        $rules = [
            'name' => 'required',
            'dob' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            User::where('id',$id)->update([
                'name' => $request->name,
                'dob' => $request->dob,
                'email' => $request->email,
            ]);
            DB::commit();
            return redirect()->back()->with('message','Personal information updated');
        }
        catch(Throwable $e)
        {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function addAddress(){
        $id = Auth::id();
        $user = User::where('id',$id)->first();
        return view('ecommerce.user.address-form',['user'=> $user]);
    }

    public function storeAddress(Request $request){

        $user_id = Auth::id();
        $rules =[
            'name' => 'required',
            'phone_number' => [
                'required',
                Rule::unique('address')->ignore($user_id)
            ],
            'address_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'address_type' => 'required',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            Address::create([
                'user_id' => $user_id,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'address_type' => $request->address_type,
            ]);
            DB::commit();
            return redirect()->route('user.address')->with('message','Address added successfully');
        }
        catch(Throwable $e)
        {
            DB::rollBack();
            return $e->getMessage();
        }

    }


    public function makeAsPrimary(Request $request){
        DB::beginTransaction();
        try{
            $user = Auth::user();
            User::markPrimaryAddress($request->address_id,$user);
            DB::commit();
            return ['success' => true];
        }catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }
    public function editAddress($address_id){
        $id = base64_decode($address_id);
        $address = Address::where('id',$id)->first();
        return view('ecommerce.user.address-edit',['address'=>$address]);
    }

    public function updateAddress(Request $request,$address_id){
        $addressId = base64_decode($address_id);
        $rules =[
            'name' => 'required',
            'phone_number' => [
                'required',
                Rule::unique('address','phone_number')->ignore($addressId),
            ],
            'address_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'address_type' => 'required',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try{
            Address::where('id',$addressId)->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'address_type' => $request->address_type,
            ]);
            DB::commit();
            return redirect()->route('user.address')->with('message','Address updated successfully');
        }
        catch(Throwable $e)
        {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function showDelete(){
        $id = Auth::id();
        $user = User::where('id',$id)->first();
        $addresses = Address::where('user_id',$id)->get();
        return view('ecommerce.user.user-delete',['user'=>$user,'addresses'=>$addresses]);
    }

    public function deleteAddress($address_id){
        $addressId = base64_encode($address_id);
        Address::where('id',$addressId)->delete();
        return redirect()->back()->with('message','Address removed. You can add a new one anytime.');
    }

    public function showChangePasswordForm(){
        $id = Auth::id();
        $user = User::where('id',$id)->first();
        return view('ecommerce.user.change-password',['user'=>$user]);
    }

    public function submitChangePasswordForm(Request $request){
        $rules = [
            'current_password' => ['required',new MatchOldPassword],
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try{
            $user = auth()->user();
            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();
            return redirect()->back()->with('message','Password changed successfully!');
        }
        catch(Throwable $e)
        {
            DB::rollBack();
            return $e->getMessage();
        }
    }

}
