<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Number;
use Session;
use View;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $product_id = $request->input('product_id');
        $product = Products::findOrFail($product_id);
        $totalQuantity = $total = $product_price= 0;
        $quantity = $request->input('quantity');
        $quantity = Number::clamp($quantity,1,$product->quantity);
        $cart = session()->get('cart',[]);

        if(isset($cart[$product_id])){
            $cart[$product_id]['quantity'] += $quantity;
            $product_price = $cart[$product_id]['price'] * $cart[$product_id]['quantity'];
            foreach($cart as $key => $details){
                $totalQuantity += $details['quantity'];
                $total += $details['price'] * $details['quantity'];
            }
            Session::put('cart',$cart);
            return [
                'success'=>false,
                'id'=>$product_id,
                'totalQuantity'=>$totalQuantity,
                'total'=>$total,
                'product_price'=>$product_price,
                'current_quantity'=>$cart[$product_id]['quantity'],
            ];

        }else{
            $cart[$product_id]=[
                'name' => $product->product_name,
                'description' => $product->description,
                'price' => $product->price,
                'quantity' => $quantity,
            ];

            foreach($cart as $key => $details){
            $totalQuantity += $details['quantity'];
            $total += $details['price'] * $details['quantity'];
            }

            Session::put('cart',$cart);
            return response()->json([
                'totalQuantity'=>$totalQuantity,
                'total'=>$total,
                'success'=>true,
                'cart'=>view('ecommerce.layouts.ajax_cart',[
                    'id'=>$product_id,
                    'name' => $product->product_name,
                    'description' => $product->description,
                    'product_price'=>$product->price,
                    'current_quantity'=>$quantity,
                    'remove_product_url' => $request->input('remove_product_url'),
                    'add_quantity_url' => $request->input('add_quantity_url'),
                    'remove_quantity_url' => $request->input('remove_quantity_url'),
                    ])->render(),
            ]);
        }
    }

    public function addQuantity($id){
        $cart = Session::get('cart',[]);
        $totalQuantity = $total = $product_price= 0;
        if(isset($cart[$id])){
            $cart[$id]['quantity']++;
            $product_price = $cart[$id]['price'] * $cart[$id]['quantity'];
        }
        foreach($cart as $key => $details){
            $totalQuantity += $details['quantity'];
            $total += $details['price'] * $details['quantity'];
        }
        Session::put('cart',$cart);
        return [
            'success'=>true,
            'id'=>$id,
            'totalQuantity'=>$totalQuantity,
            'total'=>$total,
            'product_price'=>$product_price,
            'current_quantity'=>$cart[$id]['quantity'],
        ];
    }

    public function removeAddToCart($id){
        $cart = Session::get('cart',[]);
        $totalQuantity = $total = $product_price= 0;
        if($cart[$id]['quantity'] > 1){
            --$cart[$id]['quantity'];
            $product_price = $cart[$id]['price'] * $cart[$id]['quantity'];
        }else{
            unset($cart[$id]);
        }

        foreach($cart as $key => $details){
            $totalQuantity += $details['quantity'];
            $total += $details['price'] * $details['quantity'];
        }

        Session::put('cart',$cart);
        return [
            'success'=>true,
            'id'=>$id,
            'totalQuantity'=>$totalQuantity,
            'total'=>$total,
            'product_price'=>$product_price,
            'current_quantity'=> $cart[$id]['quantity'] ?? 0,
        ];
    }

    public function removeCartProduct($id){
        $cart = Session::get('cart',[]);
        $totalQuantity = $total = $product_price= 0;
        unset($cart[$id]);

        foreach($cart as $key => $details){
            $totalQuantity += $details['quantity'];
            $total += $details['price'] * $details['quantity'];
        }

        Session::put('cart',$cart);
        return [
            'success'=>true,
            'id'=>$id,
            'totalQuantity'=>$totalQuantity,
            'total'=>$total,
            'product_price'=>$product_price,
            'current_quantity'=> $cart[$id]['quantity'] ?? 0,
        ];
    }
}
