<?php

namespace App\Models;

class PortfolioProject extends ContentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'excerpt',
        'content',
        'client_name',
        'project_date',
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
            'project_date' => 'date',
        ]);
    }

    /**
     * @var array<int, string>
     */
    protected array $draftableManyToMany = [
        'products',
        'services',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $draftablePivotAttributes = [
        'products' => ['sort_order'],
        'services' => ['sort_order'],
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('sort_order');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot('sort_order');
    }
}
