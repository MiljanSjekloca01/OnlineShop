@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>All Messages</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index')}}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">All Messages</div>
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
                    <a class="tf-button style-1 w208" href="{{ route('admin.coupon.add')}}"><i
                            class="icon-plus"></i>Add new</a>
                </div>
                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        @if(Session::has('status'))
                            <p class="alert alert-success">
                                {{ Session::get('status') }}
                            </p>
                        @endif
                        <table class="table table-striped table-bordered table-messages">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Is Read</th>
                                    <th>Date</th>
                                    <th>Read / Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contacts as $contact)
                                <tr>
                                    <td>{{ $contact->id }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>{{ $contact->email }}</td>
                                    <td>{{ $contact->comment }}</td>
                                    <td>{{ $contact->is_read ? 'Yes' : 'No' }}</td>
                                    <td>{{ $contact->created_at }}</td>
                                    <td>
                                        <div class="list-icon-function">
                                            <form action="{{ route('admin.contacts.read', $contact) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <label class="switch">
                                                    <input type="checkbox" name="is_read" onchange="this.form.submit()" {{ $contact->is_read ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </form>
                                            <form action="{{ route('admin.contacts.delete',$contact)}}" method="POST">
                                            @csrf
                                            @method('DELETE')    
                                                <div class="item text-danger delete">
                                                    <i class="icon-trash-2"></i>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>                                 
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $contacts->links('pagination::bootstrap-5')}}
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        $(function() {
            $('.delete').on('click',function(e){
                e.preventDefault();
                var form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "Are you sure u want to delete this record ?",
                    type: "warning",
                    buttons:["No","Yes"],
                    confirmButtonColor:"#dc3545"
                }).then(function(result){
                    if(result){
                        form.submit();
                    }
                })
            });
        });
    </script>
@endpush


@push('styles')
    <style>
        .table-messages{
            width:auto;
        }

        .table-striped th:nth-child(2), .table-striped td:nth-child(2) {
            width: auto;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 30px; /* možeš smanjiti */
            height: 15px;
            vertical-align: middle;
        }
        .switch input { display: none; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 15px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 11px;
            width: 11px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: #4caf50; }
        input:checked + .slider:before { transform: translateX(15px); }
    </style>
@endpush