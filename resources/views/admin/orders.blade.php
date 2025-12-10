@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Orders</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index')}} ">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Orders</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search">
                        <fieldset class="name">
                            <input type="text" placeholder="Search here..." class="" name="name"
                                tabindex="2" value="" aria-required="true" required="">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:70px">OrderNo</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Total</th>

                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Total Items</th>
                                <th>Delivered On</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->name }}</td>
                                <td>{{ $order->phone }}</td>
                                <td>${{ $order->subtotal }}</td>
                                <td>${{ $order->tax }}</td>
                                <td>${{ $order->total }}</td>

                                <td>{{ $order->status }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->orderItems->count() }}</td>
                                <td>{{ $order->delivered_date ? $order->delivered_date : "To be delivered"}}</td>
                                <td>
                                    <a href="{{ route('admin.order.details',$order)}}">
                                        <div class="list-icon-function view-icon">
                                            <div class="item eye">
                                                <i class="icon-eye"></i>
                                            </div>
                                        </div>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $orders->links('pagination::bootstrap-5')}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <style>
        .wg-table table th,
        .wg-table table td{
            text-align: center;
            font-weight: 600;
        }
    </style>
@endpush