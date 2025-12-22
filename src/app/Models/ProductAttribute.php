<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ProductAttribute extends Model
{
    use HasFactory;
    use HasSlug;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'sort_order',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_attribute')->withPivot('sort_order');
    }
}
