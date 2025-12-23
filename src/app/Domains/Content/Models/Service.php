<?php

namespace App\Domains\Content\Models;

use App\Domains\Shared\Models\ContentModel;

class Service extends ContentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'short_description',
        'content',
        'sort_order',
        'is_published',
        'published_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $draftableManyToMany = [
        'portfolioProjects',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $draftablePivotAttributes = [
        'portfolioProjects' => ['sort_order'],
    ];

    public function portfolioProjects()
    {
        return $this->belongsToMany(PortfolioProject::class)->withPivot('sort_order');
    }
}
