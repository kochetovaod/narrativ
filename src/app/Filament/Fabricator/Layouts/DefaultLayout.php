<?php

namespace App\Filament\Fabricator\Layouts;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageLayouts\PageLayout;

class DefaultLayout extends PageLayout
{
    protected static ?string $name = 'default';

    protected static ?string $label = 'Базовый макет';

    protected static ?string $icon = 'heroicon-o-rectangle-group';

    public function schema(): array
    {
        return [
            Section::make('Шапка страницы')
                ->schema([
                    TextInput::make('heading')
                        ->label('Заголовок (H1)')
                        ->maxLength(255),
                    Textarea::make('intro')
                        ->label('Краткое описание')
                        ->rows(3),
                ])
                ->columns(1),
        ];
    }
}
