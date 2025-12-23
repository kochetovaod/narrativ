@php
    $blockData = $data ?? ($block['data'] ?? []);
@endphp

<section class="prose max-w-none">
    @if ($title = data_get($blockData, 'title'))
        <h2>{{ $title }}</h2>
    @endif

    @if ($lead = data_get($blockData, 'lead'))
        <p class="text-lg text-gray-600">{{ $lead }}</p>
    @endif

    @if ($content = data_get($blockData, 'content'))
        <div class="prose max-w-none">{!! $content !!}</div>
    @endif
</section>
