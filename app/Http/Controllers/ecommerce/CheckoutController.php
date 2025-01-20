<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDescription;
use App\Models\OrderStatus;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Session;
use Throwable;
use Validator;

class CheckoutController extends Controller
{
    public function index(){
        return view('ecommerce.checkout.checkout');
    }

    public function placeOrder(Request $request){
        $validator = Validator::make($request->all(),['address_id'=>'required'],['required'=>'This is a required Field']);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }
        $user_id = Session::get('session_user');
        foreach($user_id as $key => $value){
            $id = $key;
        }

        $i = Session::get('cart');
            $totalQuantity = 0;
            $total = 0;
            foreach ($i as $product_id => $item) {
                $totalQuantity += $item['quantity'];
                $total += $item['price'] * $item['quantity'];
            }

        DB::beginTransaction();
        try{
            $order = Order::create([
                'user_id' => $id,
                'address_id' => $request->address_id,
                'order_status_id' => 1,
                'total_quantity' => $totalQuantity,
                'payment_method' => $request->paymentMethod,
                'sub_total' => $total,
                'total' => $total,
            ]);

            foreach($i as $product_id => $values){
                OrderDescription::create([
                    'order_id' => $order->id,
                    'product_id' => $product_id,
                    'quantity' => $values['quantity'],
                    'total' => $total,
                ]);
            }

            DB::commit();
            return redirect()->route('user.index')->with('message',"Order placed Successfully");
        }
        catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
