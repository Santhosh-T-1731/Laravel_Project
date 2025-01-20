<?php

namespace App\Http\Controllers\ecommerce\admin;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\Address;
use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Throwable;
use Validator;

class CustomerController extends Controller
{
    public function index(){
        $data = [];
        $data['customers'] = User::where('role_id','!=',1)->select('id','name','email','dob','status')->get();
        return view('ecommerce.admin.customers.customers',$data);
    }

    public function createCustomer(){
        return view('ecommerce.admin.customers.add-customer');
    }

    public function viewCustomer($customer_id){
        $customerId = base64_decode($customer_id);
        $data['customers'] = $customers = User::with('addresses')->where('id',$customerId)->get();
        return view('ecommerce.admin.customers.view-customer',$data);
    }

    public function checkEmailAvailability(Request $request){
        $email = $request->input('email');
        $user = User::where('email',$email)->first();
        if($user){
            return response()->json(['email'=> false]);
        }else{
            return response()->json(['email'=> true]);
        }
    }

    public function checkPhoneNumber(Request $request){
        $phone_number = $request->input('phone_number');
        $exists = Address::where('phone_number',$phone_number)->exists();
        if($exists){
            return response()->json(['phone_number'=>false]);
        }else{
            return response()->json(['phone_number'=> true]);
        }
    }

    public function storeCustomer(Request $request){
        $rules = [
            'customer_name' => 'required',
            'email' => 'required|unique:users,email',
            'dob' => 'required',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'name' => 'required',
            'phone_number' => 'required|unique:address,phone_number',
            'address_1' => 'required',
            'zip_code' => 'required',
            'city' => 'required',
            'state' => 'required',
            'address_type' => 'required',
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            $user = User::create([
                'name'=>$request->customer_name,
                'email' => $request->email,
                'dob' => $request->dob,
                'password' => Hash::make($request->password),
            ]);

            Address::create([
                'user_id' => $user->id,
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
            Mail::to($user->email)->send(new VerifyEmail($user));
            DB::commit();
            return redirect()->route('admin.customer.index')->with('message','Account creation was successful and a verification link is sent to the registered mail address');
        }
        catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }

    }

    public function destroy($customer_id){
      $customerId = base64_decode($customer_id);
      DB::beginTransaction();
      try{
        Address::where('user_id',$customerId)->delete();
        User::where('id',$customerId)->delete();
        DB::commit();
        return redirect()->back()->with('message',"Customer account was deleted");
      }catch(Throwable $e){
        DB::rollBack();
        return $e->getMessage();
      }
    }
}
