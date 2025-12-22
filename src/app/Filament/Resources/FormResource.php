<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Filament\Resources\FormResource\RelationManagers\FormFieldsRelationManager;
use App\Filament\Resources\FormResource\RelationManagers\FormSubmissionsRelationManager;
use App\Enums\Permission;
use App\Models\Form;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Формы';

    protected static ?string $modelLabel = 'Форма';

    protected static ?string $pluralModelLabel = 'Формы';

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
                        Forms\Components\TagsInput::make('recipients')
                            ->label('Email получателей')
                            ->placeholder('admin@example.com')
                            ->helperText('Список адресов для уведомлений о заявках')
                            ->required()
                            ->separator(';')
                            ->suggestions([]),
                        Forms\Components\Textarea::make('success_message')
                            ->label('Сообщение об успешной отправке')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Антиспам и уведомления')
                    ->schema([
                        Forms\Components\Toggle::make('settings.enable_turnstile')
                            ->label('Включить Turnstile')
                            ->default(Form::DEFAULT_SETTINGS['enable_turnstile']),
                        Forms\Components\Toggle::make('settings.enable_honeypot')
                            ->label('Включить honeypot')
                            ->default(Form::DEFAULT_SETTINGS['enable_honeypot']),
                        Forms\Components\Toggle::make('settings.allow_files')
                            ->label('Разрешить файлы')
                            ->default(Form::DEFAULT_SETTINGS['allow_files']),
                        Forms\Components\TextInput::make('settings.webhook_url')
                            ->label('Webhook URL')
                            ->url()
                            ->maxLength(2048)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('settings.email_reply_to_field')
                            ->label('Поле для Reply-To')
                            ->helperText('Имя поля с email из формы')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('settings.email_template')
                            ->label('Email шаблон')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('settings.rate_limit_per_ip')
                            ->label('Лимит IP')
                            ->numeric()
                            ->default(Form::DEFAULT_SETTINGS['rate_limit_per_ip'])
                            ->minValue(0),
                        Forms\Components\TextInput::make('settings.rate_limit_per_form')
                            ->label('Лимит формы')
                            ->numeric()
                            ->default(Form::DEFAULT_SETTINGS['rate_limit_per_form'])
                            ->minValue(0),
                        Forms\Components\TextInput::make('settings.rate_limit_decay_seconds')
                            ->label('Окно rate limit, сек')
                            ->numeric()
                            ->default(Form::DEFAULT_SETTINGS['rate_limit_decay_seconds'])
                            ->minValue(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Слаг')
                    ->searchable(),
                Tables\Columns\TagsColumn::make('recipients')
                    ->label('Получатели'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активность'),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => static::canForceDelete())
                    ->authorize(fn () => static::canForceDelete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => static::canForceDelete())
                        ->authorize(fn () => static::canForceDelete()),
                ]),
            ])
            ->defaultSort('title');
    }

    public static function getRelations(): array
    {
        return [
            FormFieldsRelationManager::class,
            FormSubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageForms::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function canForceDelete(): bool
    {
        return Auth::user()?->can(Permission::ForceDeleteContent->value) ?? false;
    }
}
