<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function getAllProducts();
    public function getProductById(int $productId);
    public function createProduct(array $productData);
    public function updateProduct(int $productId, array $productData);
    public function deleteProduct(int $productId);
}
