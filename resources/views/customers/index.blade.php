@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Customers')


@section('actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="mdi mdi-account-plus-outline"></i> Add Customer</a>
@endsection

@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                @include('partials.master-filters', [
                    'placeholder' => 'Search by name, email, phone or address',
                ])
            </div>

            <div class="table-scroll" data-infinite-scroll>
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><i class="mdi mdi-account-outline"></i> Name</th>
                            <th><i class="mdi mdi-phone-outline"></i> Phone</th>
                            <th><i class="mdi mdi-email-outline"></i> Email</th>
                            <th><i class="mdi mdi-map-marker-outline"></i> Service address</th>
                            <th><i class="mdi mdi-toggle-switch-outline"></i> Status</th>
                            <th><i class="mdi mdi-cog-outline"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody data-infinite-body>
                        @include('customers.rows')
                        @if ($customers->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No customers yet. <a href="{{ route('customers.create') }}">Add your first customer</a>.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $customers])
            </div>
        </div>
    </div>
@endsection
