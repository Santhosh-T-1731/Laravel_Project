<?php

namespace App\Http\Controllers\ecommerce\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\OrderDescription;
use App\Models\OrderStatus;
use DB;
use Illuminate\Http\Request;
use Throwable;
use Validator;

class AdminOrderController extends Controller
{
    public function listAllOrders(){
        $data['orders'] = Order::with('user','address','orderDescriptions','status')->get();
        $data['orderStatuses'] = OrderStatus::pluck('id','name')->toArray();
        return view('ecommerce.admin.orders.orders',$data);
    }

    public function viewOrder($order_id)
    {
        $data = [];
        $data['order_id'] = $orderId = base64_decode($order_id);
        $data['orders'] = $orders = Order::with(['status','orderDescriptions','address'])->where('id',$orderId)->get();
        $data['orderDescriptions'] = $orderDescriptions = OrderDescription::with('products')->where('order_id',$orderId)->get();
        $data['orderCancelReason'] = OrderCancelReason::where('order_id',$orderId)->first();
        return view('ecommerce.admin.orders.order-details',$data);
    }

    // public function changeOrderStatus(Request $request,$order_id){
    //     $orderId = base64_decode($order_id);

    //     $rules = [
    //         'orderStatus' => 'required',
    //         'cancellationReason' => 'required_if:orderStatus,6'
    //     ];
    //     $messages = [
    //         'orderStatus.required' => 'Order Status is a required one',
    //         'cancellationReason.required_if' => 'Reason must be entered for order cancellation'
    //     ];
    //     $validator = Validator::make($request->all(),$rules,$messages);
    //     if($validator->fails()){
    //         return back()->withErrors($validator)->withInput();
    //     }
    //     DB::beginTransaction();
    //     try{
    //         Order::where('id',$orderId)->update([
    //             'order_status_id' => $request->orderStatus,
    //         ]);

    //         if($request->orderStatus == 6){
    //             OrderCancelReason::create([
    //                 'order_id' => $orderId,
    //                 'reason' => $request->cancellationReason,
    //             ]);
    //         }
    //         DB::commit();
    //         return redirect()->back()->with('message','Order Status Updated');
    //     }catch(Throwable $e){
    //         DB::rollBack();
    //         return $e->getMessage();
    //     }
    // }

    public function changeOrderStatus(Request $request){
        $orderId = $request->input('order_id');
        DB::beginTransaction();
        try{
            Order::where('id',$orderId)->update([
                'order_status_id' => $request->orderStatus,
            ]);
            DB::commit();
            return ['success'=>true];
        }catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function cancelOrder(Request $request){
        $orderId = $request->input('order_id');
        // $rules = [
        //     'cancellationReason' => 'required_if:orderStatus,6'
        // ];
        // $messages = [
        //     'cancellationReason.required_if' => 'Reason must be entered for order cancellation'
        // ];
        // $validator = Validator::make($request->all(),$rules,$messages);
        // if($validator->fails()){
        //     return back()->withErrors($validator)->withInput();
        // }
        DB::beginTransaction();
        try{

        OrderCancelReason::create([
            'order_id' => $orderId,
            'reason' => $request->cancellationReason,
        ]);

        DB::commit();
        return redirect()->back()->with('message','Order Status Updated');
        }catch(Throwable $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
