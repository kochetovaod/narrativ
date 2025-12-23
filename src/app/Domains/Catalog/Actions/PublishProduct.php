<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;

class PublishProduct
{
    public function __invoke(Product $product): Product
    {
        $product->forceFill([
            'is_published' => true,
            'published_at' => $product->published_at ?? now(),
        ])->save();

        return $product;
    }
}
