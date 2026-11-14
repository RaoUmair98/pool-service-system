@extends('layouts.admin')

@section('title', $pool->exists ? 'Edit Pool' : 'Add Pool')


@section('content')
    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                <form method="POST"
                      action="{{ $pool->exists ? route('pools.update', $pool) : route('customers.pools.store', $customer) }}"
                      enctype="multipart/form-data"
                      id="pool-form">
                    @csrf
                    @if ($pool->exists)
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="span-3">
                            <x-ui.field name="name" label="Pool Name" icon="mdi-tag-outline" hint="Optional. e.g. Main, Spa.">
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $pool->name) }}" autofocus>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="type" label="Type" required icon="mdi-water">
                                <select id="type" name="type" class="form-control" required>
                                    @foreach (\App\Models\Pool::TYPES as $value => [$label])
                                        <option value="{{ $value }}" @selected(old('type', $pool->type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="gallons" label="Size (gallons)" icon="mdi-cup-water">
                                <input type="number" id="gallons" name="gallons" class="form-control" min="1" max="1000000" value="{{ old('gallons', $pool->gallons) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="monthly_rate" label="Monthly Rate ({{ $customer->company->currency ?? 'USD' }})" required icon="mdi-currency-usd">
                                <input type="number" id="monthly_rate" name="monthly_rate" class="form-control" min="0" step="0.01" value="{{ old('monthly_rate', $pool->monthly_rate) }}" required>
                            </x-ui.field>
                        </div>
                        <div class="span-12">
                            <x-ui.field name="photo" label="Photo" icon="mdi-image-outline" hint="PNG or JPG, max 4 MB.">
                                <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                                    @if ($pool->photoUrl())
                                        <img src="{{ $pool->photoUrl() }}" alt="Pool photo" style="max-height: 72px; max-width: 140px;" class="border rounded p-1 bg-white">
                                        <div class="custom-control custom-checkbox mb-0">
                                            <input type="checkbox" class="custom-control-input" id="remove_photo" name="remove_photo" value="1" @checked(old('remove_photo'))>
                                            <label class="custom-control-label" for="remove_photo">Remove photo</label>
                                        </div>
                                    @endif
                                    <input type="file" id="photo" name="photo" class="form-control-file" accept="image/*">
                                </div>
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="section-divider"></div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="section-label mb-0"><i class="mdi mdi-wrench-outline"></i> Equipment</div>
                        <button type="button" class="btn btn-sm btn-light" id="add-equipment"><i class="mdi mdi-plus"></i> Add row</button>
                    </div>
                    <p class="text-muted small mb-2">Pump, filter, heater, cleaner — anything useful for the technician.</p>

                    <div id="equipment-rows">
                        @foreach (old('equipment', $equipment) as $i => $row)
                            <div class="equipment-row">
                                <div>
                                    <input type="text" name="equipment[{{ $i }}][name]" class="form-control" placeholder="Name" value="{{ $row['name'] ?? '' }}">
                                </div>
                                <div class="equip-notes">
                                    <input type="text" name="equipment[{{ $i }}][notes]" class="form-control" placeholder="Notes (optional)" value="{{ $row['notes'] ?? '' }}">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-light btn-block remove-equipment" title="Remove row"><i class="mdi mdi-close"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @include('partials.form-actions', [
                        'model' => $pool,
                        'cancel' => $pool->exists ? route('pools.show', $pool) : route('customers.show', $customer),
                        'saveAndNew' => true,
                        'activeHint' => 'Turned-off pools are hidden from new schedules and invoices.',
                    ])
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var rows = document.getElementById('equipment-rows');
    var index = rows.querySelectorAll('.equipment-row').length;

    document.getElementById('add-equipment').addEventListener('click', function () {
        var div = document.createElement('div');
        div.className = 'equipment-row';
        div.innerHTML =
            '<div><input type="text" name="equipment[' + index + '][name]" class="form-control" placeholder="Name"></div>' +
            '<div class="equip-notes"><input type="text" name="equipment[' + index + '][notes]" class="form-control" placeholder="Notes (optional)"></div>' +
            '<div><button type="button" class="btn btn-light btn-block remove-equipment" title="Remove row"><i class="mdi mdi-close"></i></button></div>';
        rows.appendChild(div);
        index++;
    });

    rows.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-equipment');
        if (!btn) return;
        var row = btn.closest('.equipment-row');
        if (rows.querySelectorAll('.equipment-row').length === 1) {
            row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
            return;
        }
        row.remove();
    });
})();
</script>
@endpush
