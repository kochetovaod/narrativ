<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class HeroBlock extends PageBlock
{
    protected static ?string $name = 'hero';

    protected static ?string $label = 'Hero блок';

    protected static ?string $icon = 'heroicon-o-sparkles';

    public function schema(): array
    {
        return [
            TextInput::make('title')
                ->label('Заголовок')
                ->required()
                ->maxLength(255),
            Textarea::make('subtitle')
                ->label('Подзаголовок')
                ->rows(3),
            TextInput::make('cta_label')
                ->label('Текст кнопки')
                ->maxLength(255),
            TextInput::make('cta_url')
                ->label('Ссылка кнопки')
                ->url()
                ->maxLength(255),
        ];
    }

    public function getView(): string
    {
        return 'filament-fabricator::blocks.hero';
    }
}
