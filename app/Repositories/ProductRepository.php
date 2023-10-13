<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllProducts()
    {
        return Product::query();
    }

    public function getProductByCode($code)
    {
        return Product::where('code', $code);
    }

    public function addProduct($params)
    {
        return Product::firstOrCreate(
            ['code' => $params['code']],
            $params
        );
    }

    public function turnRunToFalse($product)
    {
        return $product->update(['will_run' => 0]);
    }
}
