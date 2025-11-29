<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    public function index(){
        $items = Cart::instance('cart')->content();
        return view('cart',compact('items'));
    }

    public function addToCart(Request $request){
        Cart::instance('cart')->add(
            $request->id,
            $request->name,
            $request->quantity,
            $request->price
            )->associate(Product::class);
        return redirect()->back();
    }


    public function increaseCartQuantity($rowId){
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function decreaseCartQuantity($rowId){
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function removeItem($rowId){
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    public function emptyCart(){
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }

    // Coupon

    public function apply_coupon_code(Request $request){
        $coupon_code = $request->coupon_code;
        if(isset($coupon_code)){
            $coupon = Coupon::where('code',$coupon_code)
            ->where('expiry_date','>=',Carbon::today())
            ->where('cart_value','<=',(float)Cart::instance('cart')->subtotal())
            ->first();
            if(!$coupon){
                return redirect()->back()->with('error','Invalid coupon code');
            }else{
                Session::put('coupon',[
                    'code' => $coupon->code,
                    'type' => $coupon->type, 
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value
                ]);

                $this->calculateDiscount();
                return redirect()->back()->with('success','Coupon has been applied!');
            }
        }else{
            return redirect()->back()->with('error','Invalid coupon code');
        }
    }

    public function calculateDiscount(){
        $discount = 0;
        if(Session::has('coupon')){
            if(Session::get('coupon')['type'] == 'fixed'){
                $discount = Session::get('coupon')['value'];
            }else{
                $discount = (Session::get('coupon')['value']/100) * (float)Cart::instance('cart')->subtotal();
            }

            $subtotalAfterDiscount = (float)Cart::instance('cart')->subtotal() - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax'))/100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts',[
                'discount' => number_format(floatval($discount),2,'.',''),
                'subtotal' => number_format(floatval($subtotalAfterDiscount),2,'.',''),
                'tax' => number_format(floatval($taxAfterDiscount),2,'.',''),
                'total' => number_format(floatval($totalAfterDiscount),2,'.',''),
            ]);
        }
    }

    public function remove_coupon_code(){
        Session::forget('coupon');
        Session::forget('discounts');
        return back()->with('success','Coupon has been removed!');
    }

    //checkout

    public function checkout(){
        //if(!Auth::check()){ return redirect()->route('login'); }
        
        if (Cart::instance('cart')->count() == 0) { return redirect()->route('cart.index') ->with('message', 'You need to select at least one item to proceed to checkout.'); }
        
        $address = Address::where('user_id',Auth::user()->id)->where('isdefault',1)->first();
        return view('checkout',compact('address'));
    }

}
