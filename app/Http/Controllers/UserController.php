<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    public function index(){
        return view("user.index");
    }

    // User orders
    public function orders(){
        $orders = Order::where('user_id',Auth::user()->id)
            ->orderBy('created_at','DESC')->paginate(10);
        return view('user.orders',compact('orders'));
    }


    public function order_details(Order $order){
        $orderItems = $order->orderItems()->paginate(12);
        $transaction = $order->transaction;
        return view('user.order-details',compact('order','orderItems','transaction'));
    }

    public function cancel_order(Order $order){
        if($order->status == 'ordered'){
            $order->status = 'canceled';
            $order->canceled_date = Carbon::now();
            $order->save();
            if ($order->transaction) {
                $order->transaction->status = 'declined';
                $order->transaction->save();
            }
            
            return back()->with('status','Your order has been canceled');
        }else{
            return back()->with('status','You cannot cancel this order!');
        }
    }
}
