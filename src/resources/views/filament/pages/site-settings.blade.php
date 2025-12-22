<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                Сохранить
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
