<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class FeatureListBlock extends PageBlock
{
    protected static ?string $name = 'feature-list';

    protected static ?string $label = 'Список преимуществ';

    protected static ?string $icon = 'heroicon-o-check-badge';

    public function schema(): array
    {
        return [
            TextInput::make('title')
                ->label('Заголовок блока')
                ->maxLength(255),
            Repeater::make('items')
                ->label('Пункты')
                ->schema([
                    TextInput::make('title')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Описание')
                        ->rows(2),
                ])
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->columnSpanFull(),
        ];
    }

    public function getView(): string
    {
        return 'filament-fabricator::blocks.feature-list';
    }
}
