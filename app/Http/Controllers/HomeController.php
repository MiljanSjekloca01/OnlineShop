<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::where('status',1)->take(3)->get();
        $categories = Category::orderBy('name')->get();
        $products_on_sale = Product::whereNotNull('sale_price')->where('sale_price','<>','')->inRandomOrder()->take(8)->get();
        $featured_products = Product::where('featured',1)->take(8)->get();
        return view('index',compact('slides','categories','products_on_sale','featured_products'));
    }
}
