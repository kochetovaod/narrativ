<?php

namespace App\Models;

class Capability extends ContentModel
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
}
