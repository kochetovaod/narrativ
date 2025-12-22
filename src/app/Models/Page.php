<?php

namespace App\Models;

use App\Models\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\SoftDeletes;
use Oddvalue\LaravelDrafts\Concerns\HasDrafts;
use Wildside\Userstamps\Userstamps;
use Z3d0X\FilamentFabricator\Models\Page as FabricatorPage;

class Page extends FabricatorPage
{
    use HasDrafts;
    use LogsActivityChanges;
    use SoftDeletes;
    use Userstamps;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'layout',
        'blocks',
        'seo_title',
        'seo_description',
        'is_published',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

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
}
