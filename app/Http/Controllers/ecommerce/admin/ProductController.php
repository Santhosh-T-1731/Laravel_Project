<?php

namespace App\Http\Controllers\ecommerce\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Products;
use DB;
use Illuminate\Http\Request;
use Session;
use Str;
use Throwable;
use Validator;

class ProductController extends Controller
{
    public function index()
    {
        // $products = Products::all();
        $products = Products::join('categories as parent_category','parent_category.id','products.category_id')
        ->select('products.id','products.product_name','products.product_slug','products.image','products.quantity','products.price','parent_category.name as parent_category')->get();
        return view('ecommerce.admin.products.products', ['products' => $products]);
    }

    public function create()
    {
        $categories = Category::pluck('name','id')->toArray();
        // $subCategories = $category->where('parent_id', '!=', 0)->pluck('name', 'id')->toArray();
        return view('ecommerce.admin.products.productForm', ['categories' => $categories]);
    }
    public function store(Request $request)
    {

        $rules = [
            'product_name' => 'required',
            'category_id' => 'required',
            'product_slug' => 'required|unique:products,product_slug',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
            'quantity' => 'required',
            'price' => 'required',
            'description' => 'required',
            'is_featured' => 'required',
        ];
        dd($request->all());
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $imageName='';
        if($request->image){
            $date = date('YmdHis');
            $imageName = $date . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('images/products/'), $imageName);
        }

        $slug = $request->input('product_slug', Str::slug($request->input('product_name')));
        DB::beginTransaction();
        try {
            Products::create([
                'product_name' => $request->input('product_name'),
                'product_slug' => $slug,
                'image' => $imageName,
                'category_id' => $request->input('category_id'),
                'quantity' => $request->input('quantity'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'is_featured' => $request->input('is_featured'),
            ]);
            DB::commit();
            Session::flash('message', 'Product Created Successfully');
            return redirect()->route('admin.products.index');
        } catch (Throwable $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function edit($id){
        $product = Products::where('id','=',$id)->first();
        $categories = Category::pluck('name','id')->toArray();
        return view('ecommerce.admin.products.productEdit',['product'=>$product,'categories' => $categories]);
    }

    public function update(Request $request,$id){
        $rules = [
            'product_name' => 'required',
            'category_id' => 'required',
            'product_slug' => 'required|unique:products,product_slug,'. $id,
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
            'quantity' => 'required',
            'price' => 'required',
            'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldImage = Products::findOrFail($id);
        if($request->hasFile('image'))
        {
            $date = date('YmdHis');
            $newImage = $date . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('images/products/'),$newImage);

            if($oldImage->image && file_exists(public_path('images/products/'.$oldImage->image)))
            {
                unlink(public_path('images/products/'.$oldImage->image));
            }
            $oldImage->image = $newImage;
        }
        $oldImage->save();
        $slug = $request->input('product_slug',Str::slug($request->input('product_name')));
        DB::beginTransaction();
        try{
            Products::where('id','=',$id)->update([
                'product_name' => $request->input('product_name'),
                'product_slug' => $slug,
                'category_id' => $request->input('category_id'),
                'quantity' => $request->input('quantity'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'is_featured' => $request->input('is_featured'),
            ]);
            DB::commit();
            Session::flash('message','Product Updated Successfully');
            return redirect()->route('admin.products.index');
        }
        catch(Throwable $e)
        {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function destroy($id){
        Products::where('id','=',$id)->delete();
        return back()->with('message','Product Deleted Successfully');
    }
}
