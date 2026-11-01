@props(['name', 'label', 'required' => false, 'hint' => null, 'icon' => null])

@php
    // One icon per kind of field, so the same thing looks the same on every form.
    $icon ??= [
        'name' => 'mdi-tag-outline',
        'code' => 'mdi-barcode',
        'phone' => 'mdi-phone-outline',
        'email' => 'mdi-email-outline',
        'address' => 'mdi-map-marker-outline',
        'note' => 'mdi-note-text-outline',
        'description' => 'mdi-text',
    ][$name] ?? null;
@endphp

<div class="form-group">
    <label for="{{ $name }}">@if ($icon)<i class="mdi {{ $icon }}"></i> @endif{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
    {{ $slot }}
    @if ($hint)
        <small class="form-text text-muted">{{ $hint }}</small>
    @endif
    @error($name)
        <div class="text-danger small mt-1"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</div>
    @enderror
</div>
