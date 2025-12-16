<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Category;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Contact;
use App\Models\Slide;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function index(){
        $orders = Order::orderBy('created_at','DESC')->take(10)->get();
        $dashboardData = DB::select(
            "SELECT
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                COUNT(*) AS Total,
                SUM(IF(status = 'ordered', 1, 0)) AS TotalOrdered,
                SUM(IF(status = 'delivered', 1, 0)) AS TotalDelivered,
                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                FROM orders
            ")[0];

        $monthlyData = DB::select(
            "SELECT 
                M.id As MonthNo, M.name As MonthName,
                IFNULL(D.TotalAmount,0) As TotalAmount,
                IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,
                IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
                IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount
                FROM month_names M
                LEFT JOIN( 
                SELECT 
                    Date_FORMAT(created_at, '%b') As MonthName,
                    MONTH(created_at) As MonthNo,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                    SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                    FROM orders
                    WHERE YEAR(created_at)=YEAR(NOW()) 
                    GROUP BY YEAR(created_at), 
                    MONTH(created_at),
                    DATE_FORMAT(created_at, '%b')
                    Order By MONTH(created_at))
                D On D.MonthNo = M.id
        ");

        $totalAmountM = implode(',',collect($monthlyData)->pluck('TotalAmount')->toArray());
        $orderedAmountM = implode(',',collect($monthlyData)->pluck('TotalOrderedAmount')->toArray());
        $deliveredAmountM = implode(',',collect($monthlyData)->pluck('TotalDeliveredAmount')->toArray());
        $canceledAmountM = implode(',',collect($monthlyData)->pluck('TotalCanceledAmount')->toArray());

        $totalAmount = collect($monthlyData)->sum('TotalAmount');           
        $totalOrderedAmount = collect($monthlyData)->sum('TotalOrderedAmount');           
        $totalDeliveredAmount = collect($monthlyData)->sum('TotalDeliveredAmount');           
        $totalCanceledAmount = collect($monthlyData)->sum('TotalCanceledAmount');           

        return view('admin.index', compact( 'orders', 'dashboardData', 'totalAmountM', 'orderedAmountM', 'deliveredAmountM', 'canceledAmountM', 'totalAmount', 'totalOrderedAmount', 'totalDeliveredAmount', 'totalCanceledAmount' ));
    }

    public function brands(){
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }

    public function add_brand(){
        return view('admin.brand-add');
    }

    public function brand_store(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->GenerateThumbnailImage($image,$file_name,'brands',124,124);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been created sucesfully!');
    }
    
    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }

    public function brand_update(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'. $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
                File::delete(public_path('uploads/brands').'/'.$brand->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extension;
            $this->GenerateThumbnailImage($image,$file_name,'brands',124,124);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been updated sucesfully!');

    }

    public function brand_delete($id){
        $brand = Brand::find($id);
        if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Brand has been deleted sucesfully!');
    }

    public function categories(){
        $categories = Category::orderBy('id','DESC')->paginate(10);

        return view('admin.categories',compact('categories'));
    }


    public function category_add(){

        return view('admin.category-add');
    }

    public function category_store(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->GenerateThumbnailImage($image,$file_name,'categories',124,124);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been created sucesfully!');
    }


    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function category_update(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'. $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/categories').'/'.$category->image)){
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extension;
            $this->$this->GenerateThumbnailImage($image,$file_name,'categories',124,124);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been updated sucesfully!');

    }

    public function category_delete($id){
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)){
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted sucesfully!');
    }


    // Products

    public function products(){
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function add_product(){
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();

        return view('admin.product-add',compact('categories','brands'));
    }

    public function product_store(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->image = $request->image;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = '';
        $counter = 1;

        if($request->hasFile('images')){
            $allowedFileExtensions = ['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension,$allowedFileExtensions);
                if($gcheck){
                    $gFileName = $current_timestamp . '-' . $counter . '.' . $gextension;
                    $this->GenerateProductThumbnailImage($file,$gFileName);
                    array_push($gallery_arr,$gFileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',',$gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been added successfully');
    }


    public function GenerateProductThumbnailImage($image, $imageName){
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540,689,'top');
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        $img->resize(104,104,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail.'/'.$imageName);
    }

    public function product_edit($id){
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        
        return view('admin.product-edit', compact('product','categories','brands'));
    }

    public function product_update(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug' => 'required|unique:products,slug,'.$request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/products').'/'.$product->image)){
                File::delete(public_path('uploads/products').'/'.$product->image);
            };

            if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            };

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = '';
        $counter = 1;

        if($request->hasFile('images')){

            foreach(explode((','),$product->images) as $ofile){
                if(File::exists(public_path('uploads/products').'/'.$ofile)){
                    File::delete(public_path('uploads/products').'/'.$ofile);
                };
    
                if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                    File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
                };
            }

            $allowedFileExtensions = ['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension,$allowedFileExtensions);
                if($gcheck){
                    $gFileName = $current_timestamp . '-' . $counter . '.' . $gextension;
                    $this->GenerateProductThumbnailImage($file,$gFileName);
                    array_push($gallery_arr,$gFileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',',$gallery_arr);
            $product->images = $gallery_images;
        }
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been updated successfully');
    }

    public function product_delete($id){

        $product = Product::find($id);

        if(File::exists(public_path('uploads/products').'/'.$product->image)){
            File::delete(public_path('uploads/products').'/'.$product->image);
        };

        if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        };

        
        foreach(explode((','),$product->images) as $ofile){
            if(File::exists(public_path('uploads/products').'/'.$ofile)){
                File::delete(public_path('uploads/products').'/'.$ofile);
            };

            if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            };
        }

        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted successfully');

    }

    // Coupons

    public function coupons(){
        $coupons = Coupon::orderBy('expiry_date','DESC')->paginate(12);
        return view('admin.coupons',compact('coupons'));
    }

    public function coupon_add(){
        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request){
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);
        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status','Coupon has been added successfully');
    }

    public function coupon_edit($id){
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }

    public function coupon_update(Request $request){
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);
        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status','Coupon has been updated successfully');
    }


    public function coupon_delete($id){
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Coupon has been deleted successfully');
    }


    // Orders

    public function orders(){
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders',compact('orders'));
    }

    public function order_details(Order $order){
        $orderItems = $order->orderItems()->paginate(12);
        $transaction = $order->transaction;
        return view('admin.order-details',compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string'
        ]);
    
        $order->status = $request->order_status;
    
        if ($order->status === 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($order->status === 'canceled') {
            $order->canceled_date = Carbon::now();
        }
    
        $order->save();
    
        if ($order->status === 'delivered' && $order->transaction) {
            $transaction = $order->transaction;
            $transaction->status = 'approved';
            $transaction->save();
        }
    
        return back()->with('status', 'Order status updated successfully');
    }


    // Slider
    public function slides(){
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view('admin.slides',compact('slides'));
    }

    public function add_slide(){
        return view('admin.slide-add');
    }

    public function store_slide(Request $request){
        $request->validate([
            'tagline' => 'required',
            'title'=>'required',
            'subtitle'=>'required',
            'link'=>'required',
            'status'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->GenerateThumbnailImage($image,$file_name,'slides',400,690);
        $slide->image = $file_name;
        $slide->save();

        return redirect()->route('admin.slides')->with('statuts','Slide added successfully!');
    }

    public function edit_slide(Slide $slide){
        return view('admin.slide-edit',compact('slide'));
    }

    public function update_slide(Request $request, Slide $slide){
        $request->validate([
            'tagline' => 'required',
            'title'=>'required',
            'subtitle'=>'required',
            'link'=>'required',
            'status'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/slides/').'/'.$slide->image)){
                File::delete(public_path('uploads/slides/').'/'.$slide->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extension;
            $this->GenerateThumbnailImage($image,$file_name,'slides',400,690);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status','Slide updated successfully!');
    }

    public function delete_slide(Slide $slide){
        if(File::exists(public_path('uploads/slides/').'/'.$slide->image)){
            File::delete(public_path('uploads/slides/').'/'.$slide->image);
        }
        $slide->delete();
        return back()->with('status','Slide deleted successfully!');
    }


    // Generate Thumbnail Image Function 

    public function GenerateThumbnailImage($image,$imageName,$folder,$width,$height){
        $destinationPath = public_path('uploads/' . $folder);
        $img = Image::read($image->path());
        $img->cover($width,$height,'top');
        $img->resize($width,$height,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }


    // Contact Us / Contact Us Messages

    public function contacts(){
        $contacts = Contact::orderBy('created_at','DESC')->paginate(10);
        return view('admin.contacts',compact('contacts'));
    }

    public function contact_delete(Contact $contact){
        $contact->delete();
        return back()->with('status','Contact deleted successfully!');
    }

    public function markRead(Contact $contact, Request $request){
        $contact->is_read = $request->has('is_read');
        $contact->save();
        return back();
    }

    // Search products

    public function search(Request $request){
        $query = $request->input('query');
        $results = Product::where('name','LIKE',"%{$query}%")->take(8)->get();
        return response()->json($results);
    }
}

