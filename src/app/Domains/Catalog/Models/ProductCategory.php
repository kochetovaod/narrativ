<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Shared\Models\ContentModel;

class ProductCategory extends ContentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'description',
        'sort_order',
        'is_published',
        'published_at',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
