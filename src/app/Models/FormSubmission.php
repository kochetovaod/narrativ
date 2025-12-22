<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FormSubmission extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_SPAM = 'spam';

    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Новая',
        self::STATUS_IN_PROGRESS => 'В работе',
        self::STATUS_DONE => 'Завершена',
        self::STATUS_SPAM => 'Спам',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'form_id',
        'status',
        'payload',
        'meta',
        'reply_to',
        'is_spam',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'meta' => 'array',
            'is_spam' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(FormSubmissionFile::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function attachMediaToSubmission(Media $media, ?string $fieldName = null): FormSubmissionFile
    {
        return $this->files()->create([
            'media_id' => $media->id,
            'field_name' => $fieldName,
        ]);
    }

    public function markAsSpam(): void
    {
        $this->forceFill([
            'is_spam' => true,
            'status' => self::STATUS_SPAM,
        ])->save();
    }

    public function markAsNotSpam(): void
    {
        $this->forceFill([
            'is_spam' => false,
            'status' => $this->status === self::STATUS_SPAM ? self::STATUS_NEW : $this->status,
        ])->save();
    }

    public function submittedAt(): Carbon
    {
        return $this->created_at;
    }
}
