@php
    use Illuminate\Support\Str;

    $layoutData = $layoutData ?? ($data ?? []);
    $pageModel = $page ?? null;
    $blocksData = $blocks ?? ($pageModel?->blocks ?? []);
@endphp

<div class="max-w-5xl mx-auto space-y-10 py-10">
    @if ($heading = data_get($layoutData, 'heading'))
        <header class="space-y-3">
            <h1 class="text-3xl font-bold">{{ $heading }}</h1>
            @if ($intro = data_get($layoutData, 'intro'))
                <p class="text-lg text-gray-600">{{ $intro }}</p>
            @endif
        </header>
    @endif

    @foreach ($blocksData as $block)
        @php
            $blockType = data_get($block, 'type') ?? (is_object($block) && method_exists($block, 'getType') ? $block->getType() : null);
            $blockData = data_get($block, 'data');

            if (! $blockData && is_object($block)) {
                if (method_exists($block, 'getData')) {
                    $blockData = $block->getData();
                } elseif (method_exists($block, 'data')) {
                    $blockData = $block->data();
                }
            }

            $blockData ??= [];
            $viewName = 'filament-fabricator.blocks.' . Str::of((string) $blockType)->kebab();
        @endphp

        @includeIf($viewName, ['data' => $blockData, 'block' => $block, 'page' => $pageModel])
    @endforeach
</div>
