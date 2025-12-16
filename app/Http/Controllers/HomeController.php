<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\Category;
use App\Models\Contact;
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


    public function contact_us(){
        return view('contact');
    }

    public function contact_store(Request $request){
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits_between:9,10',
            'comment' => 'required'
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->comment = $request->comment;
        $contact->save();

        return redirect()->back()->with('success', 'Your message has been sent successfully');
    }
}
