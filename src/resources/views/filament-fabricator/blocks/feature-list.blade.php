@php
    $blockData = $data ?? ($block['data'] ?? []);
    $items = data_get($blockData, 'items', []);
@endphp

<section class="space-y-6">
    @if ($title = data_get($blockData, 'title'))
        <h2 class="text-2xl font-semibold">{{ $title }}</h2>
    @endif

    @if ($items)
        <ul class="grid gap-4 md:grid-cols-2">
            @foreach ($items as $item)
                <li class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                            ✓
                        </span>
                        <div class="space-y-1">
                            @if ($itemTitle = data_get($item, 'title'))
                                <h3 class="font-semibold">{{ $itemTitle }}</h3>
                            @endif
                            @if ($itemDescription = data_get($item, 'description'))
                                <p class="text-sm text-gray-600">{{ $itemDescription }}</p>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
