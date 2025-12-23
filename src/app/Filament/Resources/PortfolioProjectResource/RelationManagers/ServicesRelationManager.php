<?php

namespace App\Filament\Resources\PortfolioProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Услуга')
                    ->relationship('services', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('pivot.sort_order')
                    ->label('Сортировка')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('pivot.sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Услуга')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextInputColumn::make('pivot.sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить услугу'),
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
            ->defaultSort('pivot.sort_order');
    }
}
