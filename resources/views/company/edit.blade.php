@extends('layouts.admin')

@section('title', 'Company Settings')


@section('content')
    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="span-4">
                            <x-ui.field name="name" label="Company Name" required icon="mdi-domain">
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                            </x-ui.field>
                        </div>
                        <div class="span-4">
                            <x-ui.field name="email" label="Company Email">
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-4">
                            <x-ui.field name="phone" label="Company Phone">
                                <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-6">
                            <x-ui.field name="address" label="Address">
                                <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $company->address) }}</textarea>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="currency" label="Currency" required icon="mdi-currency-usd">
                                <select id="currency" name="currency" class="form-control" required>
                                    @foreach (\App\Models\Company::CURRENCIES as $code => $label)
                                        <option value="{{ $code }}" @selected(old('currency', $company->currency) === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="timezone" label="Timezone" required icon="mdi-clock-outline">
                                <select id="timezone" name="timezone" class="form-control select2" required>
                                    @foreach (\App\Models\Company::TIMEZONES as $group => $zones)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($zones as $id => $label)
                                                <option value="{{ $id }}" @selected(old('timezone', $company->timezone) === $id)>{{ $label }} ({{ $id }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="span-12">
                            <x-ui.field name="logo" label="Logo" icon="mdi-image-outline" hint="PNG or JPG, max 2 MB.">
                                <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                                    @if ($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="Company logo" style="max-height: 48px; max-width: 160px;" class="border rounded p-1 bg-white">
                                        <div class="custom-control custom-checkbox mb-0">
                                            <input type="checkbox" class="custom-control-input" id="remove_logo" name="remove_logo" value="1" @checked(old('remove_logo'))>
                                            <label class="custom-control-label" for="remove_logo">Remove logo</label>
                                        </div>
                                    @endif
                                    <input type="file" id="logo" name="logo" class="form-control-file" accept="image/*">
                                </div>
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="form-actions-bar">
                        <div></div>
                        <div class="form-actions-buttons">
                            <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-content-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
