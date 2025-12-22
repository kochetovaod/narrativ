<?php

namespace App\Filament\Resources;

use App\Enums\Permission;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

abstract class ContentResource extends Resource
{
    /**
     * Base table columns for content resources.
     *
     * @return array<int, Tables\Columns\Column>
     */
    protected static function baseTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('Название')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('slug')
                ->label('Слаг')
                ->searchable(),
            Tables\Columns\IconColumn::make('is_published')
                ->label('Опубликовано')
                ->boolean(),
            Tables\Columns\TextColumn::make('published_at')
                ->label('Дата публикации')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Обновлено')
                ->dateTime()
                ->sortable(),
        ];
    }

    /**
     * Filters shared between content resources.
     *
     * @return array<int, Tables\Filters\Filter>
     */
    protected static function baseFilters(): array
    {
        return [
            TernaryFilter::make('is_published')
                ->label('Опубликовано'),
            Filter::make('drafts')
                ->label('С черновиками')
                ->query(function (Builder $query) {
                    if (method_exists($query->getModel(), 'drafts')) {
                        return $query->whereHas('drafts');
                    }

                    return $query;
                }),
            TrashedFilter::make(),
        ];
    }

    /**
     * Common form section for SEO metadata.
     */
    protected static function seoSection(): Section
    {
        return Section::make('SEO')
            ->schema([
                TextInput::make('seo_title')
                    ->label('SEO title')
                    ->maxLength(255),
                Textarea::make('seo_description')
                    ->label('SEO description')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    /**
     * Common form section for publication data.
     */
    protected static function publicationSection(): Section
    {
        return Section::make('Публикация')
            ->schema([
                Toggle::make('is_published')
                    ->label('Опубликовано')
                    ->helperText('Опубликованные записи отображаются на сайте после даты публикации'),
                DateTimePicker::make('published_at')
                    ->label('Дата публикации')
                    ->seconds(false),
            ])
            ->columns(2);
    }

    /**
     * Media section with required alt/title custom properties.
     */
    protected static function mediaSection(string $collection = 'images', bool $multiple = true): Section
    {
        return Section::make('Медиа')
            ->schema([
                SpatieMediaLibraryFileUpload::make($collection)
                    ->label('Изображения')
                    ->collection($collection)
                    ->multiple($multiple)
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->customPropertiesFields([
                        TextInput::make('alt')
                            ->label('Alt')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->customPropertiesRules([
                        'alt' => ['required', 'string', 'max:255'],
                        'title' => ['required', 'string', 'max:255'],
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Read-only meta section for timestamps and authors.
     */
    protected static function metaSection(): Section
    {
        return Section::make('Метаданные')
            ->schema([
                Placeholder::make('created_at')
                    ->label('Создано')
                    ->content(fn (?Model $record) => $record?->created_at?->toDateTimeString() ?? '—'),
                Placeholder::make('updated_at')
                    ->label('Обновлено')
                    ->content(fn (?Model $record) => $record?->updated_at?->toDateTimeString() ?? '—'),
                Placeholder::make('deleted_at')
                    ->label('Удалено')
                    ->content(fn (?Model $record) => $record?->deleted_at?->toDateTimeString() ?? '—'),
                Placeholder::make('created_by')
                    ->label('Создал')
                    ->content(fn (?Model $record) => $record?->creator?->name ?? '—'),
                Placeholder::make('updated_by')
                    ->label('Изменил')
                    ->content(fn (?Model $record) => $record?->editor?->name ?? '—'),
                Placeholder::make('deleted_by')
                    ->label('Удалил')
                    ->content(fn (?Model $record) => $record?->deleter?->name ?? '—'),
            ])
            ->columns(3)
            ->visibleOn('edit');
    }

    /**
     * Shared table actions including preview and publication toggles.
     *
     * @return array<int, Tables\Actions\Action>
     */
    protected static function tableActions(): array
    {
        return [
            static::previewAction(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('publish')
                ->label('Публиковать')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->visible(fn (Model $record) => static::canPublish() && ! $record->is_published)
                ->authorize(fn () => static::canPublish())
                ->action(fn (Model $record) => static::publishRecord($record)),
            Tables\Actions\Action::make('unpublish')
                ->label('Снять с публикации')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn (Model $record) => static::canUnpublish() && (bool) $record->is_published)
                ->authorize(fn () => static::canUnpublish())
                ->action(fn (Model $record) => static::unpublishRecord($record)),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make()
                ->visible(fn () => static::canForceDelete())
                ->authorize(fn () => static::canForceDelete()),
        ];
    }

    /**
     * Shared bulk actions for publication workflow.
     *
     * @return array<int, BulkAction|BulkActionGroup>
     */
    protected static function tableBulkActions(): array
    {
        return [
            BulkAction::make('publish')
                ->label('Публиковать')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->visible(fn () => static::canPublish())
                ->authorize(fn () => static::canPublish())
                ->action(fn (Collection $records) => $records->each(fn (Model $record) => static::publishRecord($record))),
            BulkAction::make('unpublish')
                ->label('Снять с публикации')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn () => static::canUnpublish())
                ->authorize(fn () => static::canUnpublish())
                ->action(fn (Collection $records) => $records->each(fn (Model $record) => static::unpublishRecord($record))),
            BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->visible(fn () => static::canForceDelete())
                    ->authorize(fn () => static::canForceDelete()),
            ]),
        ];
    }

    protected static function publishRecord(Model $record): void
    {
        $record->forceFill([
            'is_published' => true,
            'published_at' => $record->published_at ?? now(),
        ])->save();
    }

    protected static function unpublishRecord(Model $record): void
    {
        $record->forceFill([
            'is_published' => false,
        ])->save();
    }

    protected static function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Предпросмотр')
            ->icon('heroicon-o-eye')
            ->visible(fn () => static::canPreview())
            ->authorize(fn () => static::canPreview())
            ->url(fn (Model $record) => static::getPreviewUrl($record))
            ->openUrlInNewTab();
    }

    protected static function getPreviewUrl(Model $record): string
    {
        $slug = $record->getAttribute('slug') ?? $record->getKey();

        return url(sprintf('/preview/%s/%s', $record->getTable(), $slug));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function canPublish(): bool
    {
        return Auth::user()?->can(Permission::PublishContent->value) ?? false;
    }

    protected static function canUnpublish(): bool
    {
        return Auth::user()?->can(Permission::UnpublishContent->value) ?? false;
    }

    protected static function canPreview(): bool
    {
        return Auth::user()?->can(Permission::PreviewContent->value) ?? false;
    }

    protected static function canForceDelete(): bool
    {
        return Auth::user()?->can(Permission::ForceDeleteContent->value) ?? false;
    }
}
