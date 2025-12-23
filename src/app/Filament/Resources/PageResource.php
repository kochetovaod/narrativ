<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Domains\Content\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Forms\Components\PageBuilder;

class PageResource extends ContentResource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Контент';

    protected static ?string $modelLabel = 'Страница';

    protected static ?string $pluralModelLabel = 'Страницы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основное')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Слаг')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('layout')
                            ->label('Макет')
                            ->required()
                            ->options(fn () => FilamentFabricator::getLayouts())
                            ->searchable(),
                        PageBuilder::make('blocks')
                            ->label('Блоки страницы')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                static::mediaSection(),
                static::seoSection(),
                static::publicationSection(),
                static::metaSection(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::baseTableColumns())
            ->filters(static::baseFilters())
            ->actions(static::tableActions())
            ->bulkActions(static::tableBulkActions())
            ->defaultSort('title');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePages::route('/'),
        ];
    }
}
