<?php

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FormSubmission extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivityChanges;
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
     * @var list<string>
     */
    protected array $activitylogAttributes = [
        'form_id',
        'status',
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

    protected static function booted(): void
    {
        static::saving(function (self $submission) {
            Validator::make(
                [
                    'form_id' => $submission->form_id,
                    'status' => $submission->status,
                    'payload' => $submission->payload,
                    'meta' => $submission->meta,
                    'reply_to' => $submission->reply_to,
                    'is_spam' => $submission->is_spam,
                ],
                [
                    'form_id' => ['required', 'integer', 'exists:forms,id'],
                    'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
                    'payload' => ['required', 'array'],
                    'meta' => ['nullable', 'array'],
                    'reply_to' => ['nullable', 'email', 'max:255'],
                    'is_spam' => ['boolean'],
                ],
            )->validate();
        });
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
