<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Enums\Permission;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductCategory;
use App\Domains\Content\Models\Capability;
use App\Domains\Content\Models\News;
use App\Domains\Content\Models\PortfolioProject;
use App\Domains\Content\Models\Service;
use App\Domains\Menu\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * @var array<class-string<Model>, string>
     */
    private const LINKABLE_MODELS = [
        Service::class => 'Услуга',
        Product::class => 'Товар',
        ProductCategory::class => 'Категория товара',
        News::class => 'Новость',
        PortfolioProject::class => 'Портфолио',
        Capability::class => 'Производственная возможность',
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основное')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('parent_id')
                            ->label('Родительский пункт')
                            ->options(fn (?MenuItem $record) => $this->getParentOptions($record))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Отображать')
                            ->default(true),
                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Открывать в новой вкладке')
                            ->default(false),
                    ])
                    ->columns(2),
                Section::make('Ссылка')
                    ->schema([
                        Forms\Components\Select::make('link_type')
                            ->label('Тип ссылки')
                            ->required()
                            ->options([
                                'url' => 'URL',
                                'route' => 'Route',
                                'model' => 'Модель',
                            ])
                            ->reactive()
                            ->default('url'),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->visible(fn (Get $get) => $get('link_type') === 'url')
                            ->required(fn (Get $get) => $get('link_type') === 'url')
                            ->maxLength(2048),
                        Forms\Components\TextInput::make('route_name')
                            ->label('Route name')
                            ->visible(fn (Get $get) => $get('link_type') === 'route')
                            ->required(fn (Get $get) => $get('link_type') === 'route')
                            ->maxLength(255),
                        Forms\Components\KeyValue::make('route_parameters')
                            ->label('Параметры роута')
                            ->visible(fn (Get $get) => $get('link_type') === 'route')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('model_type')
                            ->label('Модель')
                            ->options(self::LINKABLE_MODELS)
                            ->visible(fn (Get $get) => $get('link_type') === 'model')
                            ->required(fn (Get $get) => $get('link_type') === 'model')
                            ->searchable()
                            ->reactive(),
                        Forms\Components\Select::make('model_id')
                            ->label('Запись')
                            ->options(fn (Get $get) => $this->getModelOptions($get('model_type')))
                            ->visible(fn (Get $get) => $get('link_type') === 'model')
                            ->required(fn (Get $get) => $get('link_type') === 'model')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn (Action $action) => $action
                ->label('Изменить порядок')
                ->icon('heroicon-m-arrows-up-down')
                ->tooltip('Переключите режим сортировки и перетаскивайте пункты, чтобы поменять порядок и вложенность.'),
            )
            ->modifyQueryUsing(fn ($query) => $query
                ->with('parent')
                ->orderBy('parent_id')
                ->orderBy('sort_order'),
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->formatStateUsing(fn (string $state, MenuItem $record) => sprintf('%s%s', str_repeat('— ', $record->getDepth()), $state))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('link_type')
                    ->label('Тип ссылки')
                    ->enum([
                        'url' => 'URL',
                        'route' => 'Route',
                        'model' => 'Модель',
                    ]),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Родитель'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Отображать')
                    ->boolean(),
                Tables\Columns\IconColumn::make('open_in_new_tab')
                    ->label('Blank')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Сортировка')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('link_type')
                    ->label('Тип ссылки')
                    ->options([
                        'url' => 'URL',
                        'route' => 'Route',
                        'model' => 'Модель',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить пункт'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => $this->canForceDelete())
                    ->authorize(fn () => $this->canForceDelete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => $this->canForceDelete())
                        ->authorize(fn () => $this->canForceDelete()),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * @return array<int|string, string>
     */
    protected function getParentOptions(?MenuItem $record = null): array
    {
        $options = $this->ownerRecord?->items()
            ->orderBy('sort_order')
            ->pluck('title', 'id')
            ->toArray() ?? [];

        if ($record?->id) {
            unset($options[$record->id]);
        }

        return $options;
    }

    /**
     * @return array<int|string, string>
     */
    protected function getModelOptions(?string $modelType): array
    {
        if (! $modelType || ! is_subclass_of($modelType, Model::class)) {
            return [];
        }

        return $modelType::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function canForceDelete(): bool
    {
        return Auth::user()?->can(Permission::ForceDeleteContent->value) ?? false;
    }
}
