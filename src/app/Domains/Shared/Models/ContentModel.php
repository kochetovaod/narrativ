<?php

namespace App\Domains\Shared\Models;

use App\Contracts\RequiresMediaCustomProperties;
use App\Domains\Shared\Concerns\LogsActivityChanges;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Oddvalue\LaravelDrafts\Concerns\HasDrafts;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Wildside\Userstamps\Userstamps;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

abstract class ContentModel extends Model implements HasMedia, RequiresMediaCustomProperties
{
    use HasDrafts;
    use HasFactory;
    use LogsActivityChanges;
    use InteractsWithMedia;
    use HasSlug;
    use SoftDeletes;
    use Userstamps;

    /**
     * Defines many-to-many relations that should be draft-aware.
     *
     * @var array<int, string>
     */
    protected array $draftableManyToMany = [];

    /**
     * Defines pivot attributes for draft-aware many-to-many relations.
     *
     * @var array<string, array<int, string>>
     */
    protected array $draftablePivotAttributes = [];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->applySeoDefaults();

            Validator::make(
                $model->attributesToArray(),
                [
                    'title' => ['required', 'string', 'max:255'],
                    'slug' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique($model->getTable(), 'slug')->ignore($model),
                    ],
                    'seo_title' => ['nullable', 'string', 'max:255'],
                    'seo_description' => ['nullable', 'string'],
                    'is_published' => ['boolean'],
                    'published_at' => ['nullable', 'date'],
                ],
            )->validate();
        });
    }

    protected function applySeoDefaults(): void
    {
        if (blank($this->seo_title) && filled($this->title)) {
            $this->seo_title = $this->title;
        }

        if (blank($this->seo_description)) {
            $descriptionSource = collect([
                $this->short_description ?? null,
                $this->excerpt ?? null,
                $this->description ?? null,
                $this->content ?? null,
            ])
                ->filter()
                ->map(fn (mixed $value) => is_string($value) ? trim(strip_tags($value)) : null)
                ->filter()
                ->first();

            if (filled($descriptionSource)) {
                $this->seo_description = Str::limit($descriptionSource, 160);
            }
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'is_published',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Scope a query to only include published models.
     */
    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(function ($query) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function requiredMediaCustomProperties(string $collectionName): array
    {
        if ($collectionName === 'images') {
            return [
                'alt' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
            ];
        }

        return [];
    }
}
