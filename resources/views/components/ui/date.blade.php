@props([
    'name',
    'value' => null,
    'id' => null,
    'required' => false,
    'placeholder' => 'MM/DD/YYYY',
])

@php
    $id ??= $name;
    $iso = old($name, $value);
    $iso = is_string($iso) && $iso !== '' ? $iso : (filled($iso) ? (string) $iso : '');
    $display = '';
    if ($iso !== '') {
        try {
            $display = \Illuminate\Support\Carbon::parse($iso)->format('m/d/Y');
            $iso = \Illuminate\Support\Carbon::parse($iso)->toDateString();
        } catch (\Throwable) {
            $display = $iso;
        }
    }
@endphp

<div {{ $attributes->class(['datepicker-field']) }}>
    <input type="text"
           class="form-control datepicker"
           id="{{ $id }}"
           value="{{ $display }}"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           inputmode="numeric"
           @required($required)
           data-datepicker
           data-iso-target="{{ $id }}_iso">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}_iso" value="{{ $iso }}">
    <i class="mdi mdi-calendar-month-outline" aria-hidden="true"></i>
</div>
