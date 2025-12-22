<?php

namespace App\Models;

class News extends ContentModel
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
        'sort_order',
        'is_published',
        'published_at',
    ];
}
