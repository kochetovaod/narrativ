<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use App\Enums\Permission;
use App\Models\FormSubmission;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $recordTitleAttribute = 'status';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options(FormSubmission::STATUS_LABELS)
                    ->required(),
                Forms\Components\Toggle::make('is_spam')
                    ->label('Спам')
                    ->default(false),
                Forms\Components\TextInput::make('reply_to')
                    ->label('Reply-To')
                    ->email()
                    ->maxLength(255),
                KeyValue::make('payload')
                    ->label('Payload')
                    ->disabled()
                    ->columnSpanFull(),
                KeyValue::make('meta')
                    ->label('Meta')
                    ->disabled()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('files'))
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->enum(FormSubmission::STATUS_LABELS)
                    ->colors([
                        'primary' => FormSubmission::STATUS_NEW,
                        'warning' => FormSubmission::STATUS_IN_PROGRESS,
                        'success' => FormSubmission::STATUS_DONE,
                        'danger' => FormSubmission::STATUS_SPAM,
                    ]),
                Tables\Columns\IconColumn::make('is_spam')
                    ->label('Спам')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reply_to')
                    ->label('Reply-To'),
                Tables\Columns\TextColumn::make('meta.ip')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('files_count')
                    ->label('Файлы')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Получено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(FormSubmission::STATUS_LABELS),
                TernaryFilter::make('is_spam')
                    ->label('Спам'),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Экспорт CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->action(function () {
                        $records = $this->getTableQuery()->get();

                        return $this->exportSubmissions($records);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_spam')
                    ->label('Пометить как спам')
                    ->visible(fn (FormSubmission $record) => ! $record->is_spam)
                    ->requiresConfirmation()
                    ->action(fn (FormSubmission $record) => $record->markAsSpam()),
                Tables\Actions\Action::make('unmark_spam')
                    ->label('Убрать из спама')
                    ->visible(fn (FormSubmission $record) => $record->is_spam)
                    ->requiresConfirmation()
                    ->action(fn (FormSubmission $record) => $record->markAsNotSpam()),
                Tables\Actions\Action::make('set_status')
                    ->label('Сменить статус')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(FormSubmission::STATUS_LABELS)
                            ->required(),
                    ])
                    ->action(fn (FormSubmission $record, array $data) => $record->update(['status' => $data['status']])),
                Tables\Actions\ViewAction::make()
                    ->label('Детали')
                    ->form([
                        Forms\Components\TextInput::make('reply_to')
                            ->label('Reply-To')
                            ->disabled(),
                        KeyValue::make('payload')
                            ->label('Payload')
                            ->disabled()
                            ->columnSpanFull(),
                        KeyValue::make('meta')
                            ->label('Meta')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
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
                    Tables\Actions\BulkAction::make('export')
                        ->label('Экспорт CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => $this->exportSubmissions($records)),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function exportSubmissions(Collection $records)
    {
        $rows = $records
            ->map(function (FormSubmission $submission) {
                return [
                    'id' => $submission->id,
                    'status' => FormSubmission::STATUS_LABELS[$submission->status] ?? $submission->status,
                    'is_spam' => $submission->is_spam ? 'yes' : 'no',
                    'reply_to' => $submission->reply_to,
                    'payload' => json_encode($submission->payload),
                    'meta' => json_encode($submission->meta),
                    'created_at' => $submission->created_at,
                ];
            })
            ->prepend([
                'id' => 'ID',
                'status' => 'Status',
                'is_spam' => 'Spam',
                'reply_to' => 'Reply-To',
                'payload' => 'Payload',
                'meta' => 'Meta',
                'created_at' => 'Created at',
            ]);

        $csv = $rows
            ->map(function (array $row) {
                return collect($row)
                    ->map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"')
                    ->implode(',');
            })
            ->implode("\n");

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'submissions.csv');
    }

    protected function canForceDelete(): bool
    {
        return Auth::user()?->can(Permission::ForceDeleteContent->value) ?? false;
    }
}
