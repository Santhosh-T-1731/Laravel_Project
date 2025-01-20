<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderDescription;
use App\Models\Products;
use Auth;
use DB;
use Illuminate\Http\Request;
use Number;
use Session;

class HomeController extends Controller
{
    public function index(){
        if(Auth::check()){
            if(Auth::user()->role_id == 1){
                Auth::logout();
            }
        }
        $data = [];
        $data['featured_products'] = Products::where('is_featured',1)->get();
        $data['most_popular_products'] = Products::limit(30)->get();
        $data['recent_products'] = Products::orderByDesc('id')->take(30)->get();
        $productIds = OrderDescription::distinct()->pluck('product_id');
        $data['best_selling_products'] = $best = Products::whereIn('id', $productIds)->get();
        return view('ecommerce.layouts.index',$data);
    }

    public function show($slug){
        $data = [];
        $parent = Category::where('slug',$slug)->first();
        $data['parent']= $parent;
        $sub_cats = Category::with('parent')->where('parent_id',$parent->id)->get();
        $productsBySubCat = [];
        foreach($sub_cats as $sub){
            $productsBySubCat[$sub->name][] = Products::where('category_id',$sub->id)->get();
        }
        $data['productsBySubCat']=$productsBySubCat;
        return view('ecommerce.Categories.products-list-by-category',$data);
    }

    public function subShow($parent_slug,$sub_slug){
        $data = [];
        $parent =  Category::where('slug',$sub_slug)->first();
        $data['parent'] = $parent;
        $data['products'] = Products::where('category_id',$parent->id)->get();
        return view('ecommerce.Categories.subcategory-products',$data);
    }
    public function parentShow($par_slug){
        $data = [];
        $parent =  Category::where('slug',$par_slug)->first();
        $data['parent'] = $parent;
        $data['products'] = Products::where('category_id',$parent->id)->get();
        return view('ecommerce.Categories.parent-products',$data);
    }

    public function featuredProducts(){
        $featured_products = Products::where('is_featured',1)->get();
        return view('ecommerce.Categories.featured-products',['featured_products' => $featured_products]);
    }
    public function recentProducts(){
        $recent_products = Products::orderByDesc('id')->take(30)->get();
        return view('ecommerce.Categories.recent-products',['recent_products' => $recent_products]);
    }
    public function mostPopularProducts(){
        $most_popular_products = Products::limit(30)->get();
        return view('ecommerce.Categories.most-popular-products',['most_popular_products' => $most_popular_products]);
    }

    public function bestSellingProducts(){
        $productIds = OrderDescription::distinct()->pluck('product_id');
        $data['best_selling_products'] = $best = Products::whereIn('id', $productIds)->get();
        return view('ecommerce.Categories.best-selling',$data);
    }
}
