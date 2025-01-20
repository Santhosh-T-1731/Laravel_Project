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

class CategoryController extends Controller
{
    public function index()
    {
         $categories = Category::leftJoin('categories as parent_category',function($join){
            $join->on('parent_category.id','=','categories.parent_id');
         })
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                'categories.status',
                'categories.image',
                DB::raw('COALESCE(parent_category.name,"-") as parent_name'),
            ])->get();

         $category_ids = Products::pluck('category_id','category_id')->toArray();
        return view('ecommerce.admin.category.categories', ['categories' => $categories,'category_ids'=>$category_ids]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('ecommerce.admin.category.categoryForm', ['categories' => $categories]);
    }

    public function checkSlugAvailability(Request $request){
        $slug = $request->input('slug');
        $category = Category::where('slug',$slug)->first();

        if($category){
            return response()->json(['available'=> false]);
        }else{
            return response()->json(['available'=> true]);
        }

    }

    public function store(Request $request)
    {

        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
            'status' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $imageName='';
        if($request->image){
            $date = date('YmdHis');
            $imageName = $date . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('images'), $imageName);
        }


        $slug = $request->input('slug', Str::slug($request->input('name')));
        DB::beginTransaction();
        try {
            Category::create([
                'name' => $request->input('name'),
                'slug' => $slug,
                'image' => $imageName,
                'status' => $request->status,
                'parent_id' => $request->input('parent_id'),
            ]);
            DB::commit();
            Session::flash('message', 'Category Created Successfully');
            return redirect()->route('admin.category.index');
        } catch (Throwable $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function edit($id)
    {
        $category = Category::where('id', '=', $id)->first();
        $categories = Category::all();
        return view('ecommerce.admin.category.categoryEdit', ['category' => $category, 'categories' => $categories]);
    }

    public function update(Request $request, $id)
    {
        // $rules = [
        //     'name' => 'required',
        //     'slug' => 'required|unique:categories,slug,' . $id,
        //     'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif',
        //     'status' => 'required'
        // ];

        $dbOldImage = Category::findOrFail($id);

        // $validator = Validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return back()->withErrors($validator)->withInput();
        // }

        if ($request->hasFile('image')) {
            $date = date('YmdHis');
            $newImage = $date . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('images'), $newImage);

            $oldImagePath = public_path('images/' . $dbOldImage->image);
            if ($dbOldImage->image && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $dbOldImage->image = $newImage;
        }

        $dbOldImage->save();
        $slug = $request->input('slug', Str::slug($request->input('name')));
        DB::beginTransaction();
        try {
            Category::where('id', '=', $id)->update([
                'name' => $request->name,
                'slug' => $slug,
                'status' => $request->status,
                'parent_id' => $request->parent_id,
            ]);
            DB::commit();
            Session::flash('message', 'Category Updated Successfully');
            return redirect()->route('admin.category.index');
        } catch (Throwable $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function destroy(Request $request, $id)
    {
        Category::where('id',  $id)->delete();
        Session::flash('message', 'Category Deleted Successfully');
        return back();
    }

}
