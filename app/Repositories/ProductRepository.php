<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Products; // singular, sesuai standar Laravel

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllProducts()
    {
        return Products::all();
    }

    public function getProductById(int $productId)
    {
        return Products::find($productId);
    }

    public function createProduct(array $productData)
    {
        return Products::create($productData);
    }

    public function updateProduct(int $productId, array $productData)
    {
        $product = Products::find($productId);

        if ($product) {
            $product->update($productData);
            return $product;
        }

        return null;
    }

    public function deleteProduct(int $productId)
    {
        $product = Products::find($productId);

        if ($product) {
            return $product->delete();
        }

        return false;
    }
}
