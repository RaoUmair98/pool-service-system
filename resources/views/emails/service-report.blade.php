<x-mail::message>
# Service report

Hi {{ $visit->customer->name }},

Here is the service report for **{{ $visit->pool->displayName() }}** on **{{ $visit->scheduled_date->format('l, d M Y') }}**.

**Technician:** {{ $visit->technician->name }}  
**Company:** {{ $visit->company->name }}

## Checklist

@foreach (\App\Models\Visit::CHECKLIST_ITEMS as $key => $label)
- {{ ($checklist[$key] ?? false) ? '✓' : '○' }} {{ $label }}
@endforeach

## Water readings

| Reading | Value |
|:--------|:------|
| pH | {{ $visit->reading_ph ?? '—' }} |
| Free chlorine | {{ $visit->reading_free_chlorine ?? '—' }} |
| Alkalinity | {{ $visit->reading_alkalinity ?? '—' }} |

@if ($visit->notes)
## Notes

{{ $visit->notes }}
@endif

@if ($visit->photos->isNotEmpty())
## Photos

@foreach ($visit->photos as $photo)
- [{{ \App\Models\VisitPhoto::KINDS[$photo->kind] ?? $photo->kind }}]({{ $photo->url() }})
@endforeach
@endif

Thanks,<br>
{{ $visit->company->name }}
</x-mail::message>
