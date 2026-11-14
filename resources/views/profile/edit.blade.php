@extends('layouts.admin')

@section('title', 'My Account')

@section('content')
    <div class="form-shell">
        <div class="form-grid">
            <div class="span-6">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="span-6">
                @include('profile.partials.update-password-form')
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
