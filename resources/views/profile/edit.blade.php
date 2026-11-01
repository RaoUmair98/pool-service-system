@extends('layouts.admin')

@section('title', 'My Account')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="col-lg-6">
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
