<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Domains\Content\Models\News;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Table;

class NewsResource extends ContentResource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Контент';

    protected static ?string $modelLabel = 'Новость';

    protected static ?string $pluralModelLabel = 'Новости';

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
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0),
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNews::route('/'),
        ];
    }
}
