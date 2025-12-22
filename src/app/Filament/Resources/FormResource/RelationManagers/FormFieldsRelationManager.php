<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class FormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected static ?string $recordTitleAttribute = 'label';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Метка')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Имя поля')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        modifyRuleUsing: fn (Rule $rule) => $rule->where('form_id', $this->ownerRecord->id ?? null),
                        ignoreRecord: true,
                    ),
                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->required()
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'email' => 'Email',
                        'number' => 'Number',
                        'select' => 'Select',
                        'checkbox' => 'Checkbox',
                        'file' => 'File',
                    ]),
                Forms\Components\Toggle::make('is_required')
                    ->label('Обязательное поле')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активно')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Сортировка')
                    ->numeric()
                    ->default(0)
                    ->required(),
                KeyValue::make('validation_rules')
                    ->label('Валидация')
                    ->addButtonLabel('Добавить правило')
                    ->keyLabel('Правило')
                    ->valueLabel('Параметр')
                    ->columnSpanFull(),
                Repeater::make('options')
                    ->label('Опции')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Метка')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->label('Значение')
                            ->required(),
                    ])
                    ->orderable()
                    ->collapsed()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Метка')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип'),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Обязательно')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить поле'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
