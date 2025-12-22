<?php

namespace App\Models;

class Product extends ContentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_category_id',
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'short_description',
        'content',
        'sku',
        'filters',
        'sort_order',
        'is_published',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'filters' => 'array',
        ]);
    }

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

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function portfolioProjects()
    {
        return $this->belongsToMany(PortfolioProject::class)->withPivot('sort_order');
    }
}
