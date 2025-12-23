<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioProjectResource\Pages;
use App\Domains\Content\Models\PortfolioProject;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Table;

class PortfolioProjectResource extends ContentResource
{
    protected static ?string $model = PortfolioProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Контент';

    protected static ?string $modelLabel = 'Проект';

    protected static ?string $pluralModelLabel = 'Портфолио';

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
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Краткое описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Контент')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('client_name')
                            ->label('Клиент')
                            ->maxLength(255),
                        DatePicker::make('project_date')
                            ->label('Дата проекта')
                            ->native(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('products')
                            ->label('Товары')
                            ->relationship('products', 'title')
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('services')
                            ->label('Услуги')
                            ->relationship('services', 'title')
                            ->multiple()
                            ->preload()
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
            'index' => Pages\ManagePortfolioProjects::route('/'),
        ];
    }
}
