<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;

class UnpublishProduct
{
    public function __invoke(Product $product): Product
    {
        $product->forceFill([
            'is_published' => false,
        ])->save();

        return $product;
    }
}
