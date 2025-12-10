<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

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
}
