<?php

namespace App\Filament\Resources;

use App\Domains\Catalog\Actions\PublishProduct;
use App\Domains\Catalog\Actions\UnpublishProduct;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\PortfolioProjectsRelationManager;
use App\Domains\Catalog\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ProductResource extends ContentResource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Контент';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основное')
                    ->schema([
                        Forms\Components\Select::make('product_category_id')
                            ->label('Категория')
                            ->relationship('category', 'title')
                            ->required()
                            ->preload(),
                        Forms\Components\TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Слаг')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('short_description')
                            ->label('Краткое описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Контент')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\KeyValue::make('filters')
                            ->label('Фильтры')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение')
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
            ->defaultSort('title');
    }

    protected static function publishRecord(Model $record): void
    {
        app(PublishProduct::class)($record);
    }

    protected static function unpublishRecord(Model $record): void
    {
        app(UnpublishProduct::class)($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PortfolioProjectsRelationManager::class,
        ];
    }
}
