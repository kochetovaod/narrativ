@php
    $blockData = $data ?? ($block['data'] ?? []);
@endphp

<section class="bg-gray-50 py-12 rounded-xl px-6 shadow-sm">
    <div class="space-y-4 text-center max-w-3xl mx-auto">
        @if ($title = data_get($blockData, 'title'))
            <h2 class="text-3xl font-bold">{{ $title }}</h2>
        @endif

        @if ($subtitle = data_get($blockData, 'subtitle'))
            <p class="text-lg text-gray-600">{{ $subtitle }}</p>
        @endif

        @if ($cta = data_get($blockData, 'cta_label'))
            <div>
                <a
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700"
                    href="{{ data_get($blockData, 'cta_url') }}"
                >
                    {{ $cta }}
                </a>
            </div>
        @endif
    </div>
</section>
