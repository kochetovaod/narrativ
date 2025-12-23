<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class RichTextBlock extends PageBlock
{
    protected static ?string $name = 'rich-text';

    protected static ?string $label = 'Текстовый блок';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function schema(): array
    {
        return [
            TextInput::make('title')
                ->label('Заголовок')
                ->maxLength(255),
            Textarea::make('lead')
                ->label('Краткое введение')
                ->rows(2),
            RichEditor::make('content')
                ->label('Содержимое')
                ->columnSpanFull(),
        ];
    }

    public function getView(): string
    {
        return 'filament-fabricator::blocks.rich-text';
    }
}
