@extends('layouts.admin')

@section('title', 'Company Settings')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('company.update') }}">
                        @csrf
                        @method('PUT')

                        <x-ui.field name="name" label="Company Name" required icon="mdi-domain">
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                        </x-ui.field>

                        <x-ui.field name="email" label="Company Email">
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                        </x-ui.field>

                        <x-ui.field name="phone" label="Company Phone">
                            <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                        </x-ui.field>

                        <x-ui.field name="address" label="Address">
                            <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $company->address) }}</textarea>
                        </x-ui.field>

                        <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-content-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
