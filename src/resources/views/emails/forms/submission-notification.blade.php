@component('mail::message')
# Новая заявка: {{ $form?->title ?? 'Форма' }}

@foreach($payload as $label => $value)
- **{{ $label }}:** {{ is_scalar($value) || is_null($value) ? ($value ?? '-') : json_encode($value) }}
@endforeach

@if(!empty($meta))

@component('mail::panel')
**Meta данные**

@foreach($meta as $key => $value)
- {{ $key }}: {{ is_scalar($value) || is_null($value) ? ($value ?? '-') : json_encode($value) }}
@endforeach
@endcomponent
@endif

@component('mail::panel')
ID заявки: {{ $submission->id }}
Отправлено: {{ $submission->created_at->toDateTimeString() }}
@endcomponent
@endcomponent
