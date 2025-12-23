<?php

namespace App\Filament\Resources\ProductAttributeValueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Товар')
                    ->relationship('products', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Hidden::make('pivot.product_attribute_id')
                    ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->product_attribute_id)
                    ->dehydrated()
                    ->required(),
                Forms\Components\TextInput::make('pivot.number_value')
                    ->label('Числовое значение')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Toggle::make('pivot.bool_value')
                    ->label('Флаг')
                    ->default(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Товар')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextInputColumn::make('pivot.number_value')
                    ->label('Числовое значение')
                    ->numeric(),
                Tables\Columns\ToggleColumn::make('pivot.bool_value')
                    ->label('Флаг'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить товар'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('title');
    }
}
