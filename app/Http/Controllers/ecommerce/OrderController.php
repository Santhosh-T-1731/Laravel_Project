<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\OrderCancelReason;
use DB;
use Illuminate\Http\Request;
use App\Models\OrderDescription;
use App\Models\User;
use App\Models\Address;
use App\Models\Products;
use App\Models\Order;
use Auth;
use Session;
use Throwable;
use Validator;
class OrderController extends Controller
{
    public function listOrdersByUser(){

        $data = [];
        $user_id=Auth::id();
        $data['userEmail'] = User::where('id',$user_id)->value('email');
        $data['orders'] = $orders =  Order::with('orderDescriptions','status')->where('user_id',Auth::id())->get();

        return view('ecommerce.user.my-orders',$data);
    }
    public function viewOrder($order_id)
    {
        $data = [];
        $data['order_id'] = $order_id = base64_decode($order_id);

        $data['orders'] = $orders = Order::with(['status','orderDescriptions','address'])->where('id',$order_id)->get();
        $data['orderDescriptions'] = $orderDescriptions = OrderDescription::with('products')->where('order_id',$order_id)->get();
        return view('ecommerce.user.order-details',$data);
    }

    public function reorderProduct($id){
        $product_id = base64_decode($id);
        $product = Products::findOrFail($product_id);

        $cart = session()->get('cart',[]);

        if(isset($cart[$product_id])){
            $cart[$product_id]['quantity']++;
        }else{
            $cart[$product_id]=[
                'name' => $product->product_name,
                'description' => $product->description,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }
        Session::put('cart',$cart);
        return redirect()->back()->with('message','Product successfully added to cart');
    }

    public function cancelOrder(Request $request){
        $orderId =  $request->input('cancel_order_id');
        // $validator = Validator::make($request->all(),['reason'=>'required'],['required'=>'Please enter a valid reason for Cancellation']);
        // if($validator->fails()){
        //     return back()->withErrors($validator)->withInput();
        // }

        DB::beginTransaction();
        try{

            OrderCancelReason::create([
                'order_id' => $orderId,
                'reason' => $request->input('reason'),
            ]);

            $order = Order::where('id',$orderId)->first();
            $order->order_status_id = 6;
            $order->save();
            DB::commit();
            return back()->with('message','Order has been Cancelled');
        }
        catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }

    // public function listAllOrders(){
    //     $data['orders'] = Order::with('user','address','orderDescriptions','status')->get();
    //     return view('ecommerce.admin.orders.orders',$data);
    // }
}
