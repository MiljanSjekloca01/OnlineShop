<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\Transaction;
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
        
        if (Cart::instance('cart')->content()->count() == 0) { return redirect()->route('cart.index') ->with('message', 'You need to select at least one item to proceed to checkout.'); }
        
        $address = Address::where('user_id',Auth::user()->id)->where('isdefault',1)->first();
        return view('checkout',compact('address'));
    }


    public function place_an_order(Request $request){
        $user_id = Auth::user()->id;
        $address = Address::where('user_id',$user_id)->where('isdefault',true)->first();

        $request->validate([ 'mode' => 'required|in:cod,card,paypal' ]);

        if(!$address){
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);

            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = 'Montenegro';
            $address->user_id = $user_id;
            $address->isdefault = true;
            $address->save();
        }

        $this->setAmountForCheckout();

        $order = new Order();
        $order->user_id = $user_id;
        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->zip = $address->zip;
        $order->state = $address->state;
        $order->city = $address->city;
        $order->address = $address->address;
        $order->landmark = $address->landmark;
        $order->country = $address->country;
        $order->save();

        foreach(Cart::instance('cart')->content() as $item){
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        if($request->mode == "cod"){
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->user_id = $user_id;
            $transaction->status = 'pending';
            $transaction->mode = $request->mode;
            $transaction->save();
        }elseif($request->mode == "paypal"){
            // ToDo
        }elseif($request->mode == "card"){
            // ToDo
        }


        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        // Session::put('order_id',$order->id);
        return redirect()->route('cart.order.confirmation', ['order' => $order->id]);
    }


    public function setAmountForCheckout(){
        if (Cart::instance('cart')->content()->count() == 0) {
            Session::forget('checkout');
            return;
        }

        if(Session::has('coupon')){
            Session::put('checkout',[
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'tax' => Session::get('discounts')['tax'],
                'total' => Session::get('discounts')['total']
            ]);
        }else{
            Session::put('checkout',[
                'discount' => 0,
                'subtotal' => Cart::instance('cart')->subtotal(),
                'tax' => Cart::instance('cart')->tax(),
                'total' => Cart::instance('cart')->total()
            ]);
        }
    }


    public function order_confirmation(Order $order){
    /* Order id preko Sessiona 
       if(Session::has('order_id')){ $order = Order::find(Session::get('order_id')); return view('order-confirmation',compact('order'));
        }else{ return redirect()->route('cart.index'); }
    */
        if ($order->user_id !== Auth::user()->id) {
            abort(403);
        }

        return view('order-confirmation', compact('order'));
    }

}
