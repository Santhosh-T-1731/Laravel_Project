<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Session;

class WishlistController extends Controller
{
    public function addToWishlist(Request $request){
        $productId = $request->input('product_id');
        $product = Products::findOrFail($productId);

        $wishlist = Session::get('wishlist',[]);
        if(!isset($wishlist[$productId])){
            $wishlist[$productId] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'description' => $product->description,
                'price' => $product->price,
            ];
            Session::put('wishlist',$wishlist);
            return [
                'success'=>true,
                'wishlist'=>view('ecommerce.layouts.ajax_wishlist',[
                    'id'=>$product->id,
                    'name' => $product->product_name,
                    'description' => $product->description,
                    'price' => $product->price,
                ])->render(),
            ];
        }else{
            unset($wishlist[$productId]);
            Session::put('wishlist',$wishlist);
            return response()->json([
                'success'=> false,
                'id'=>$product->id,
            ]);
        }
    }

    public function removeWishlistProduct($id){
        $wishlist = Session::get('wishlist',[]);
        unset($wishlist[$id]);
        Session::put('wishlist',$wishlist);
        return [
            'success'=>true,
            'id'=>$id,
        ];
    }
}
