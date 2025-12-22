<?php

namespace App\Models;

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
