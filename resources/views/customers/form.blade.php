@extends('layouts.admin')

@section('title', $customer->exists ? 'Edit Customer' : 'Add Customer')

@section('content')
    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
                    @csrf
                    @if ($customer->exists)
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="span-6">
                            <x-ui.field name="name" label="Customer Name" required icon="mdi-account-outline">
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required autofocus>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="phone" label="Phone">
                                <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="email" label="Email" hint="For reports and invoices.">
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-6">
                            <x-ui.field name="service_address" label="Service Address" required icon="mdi-map-marker-outline">
                                <textarea id="service_address" name="service_address" class="form-control" rows="2" required>{{ old('service_address', $customer->service_address) }}</textarea>
                            </x-ui.field>
                        </div>
                        <div class="span-6">
                            <x-ui.field name="access_notes" label="Access Notes" icon="mdi-key-variant"
                                hint="Gate code, dog on site, lockbox, parking.">
                                <textarea id="access_notes" name="access_notes" class="form-control" rows="2">{{ old('access_notes', $customer->access_notes) }}</textarea>
                            </x-ui.field>
                        </div>
                    </div>

                    @include('partials.form-actions', [
                        'model' => $customer,
                        'cancel' => $customer->exists ? route('customers.show', $customer) : route('customers.index'),
                        'saveAndNew' => true,
                        'activeHint' => 'Turned-off customers are hidden from new schedules and invoices.',
                    ])
                </form>
            </div>
        </div>
    </div>
@endsection
