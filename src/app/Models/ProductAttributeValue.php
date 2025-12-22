<?php

namespace App\Models;

use App\Models\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;
    use LogsActivityChanges;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_attribute_id',
        'value',
        'sort_order',
    ];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value_product')
            ->withPivot('product_attribute_id', 'number_value', 'bool_value')
            ->withTimestamps();
    }
}
