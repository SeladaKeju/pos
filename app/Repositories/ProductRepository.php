<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Products; // singular, sesuai standar Laravel
use Illuminate\Support\Str;

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
        // Auto-generate SKU jika tidak ada
        if (empty($productData['sku'])) {
            $productData['sku'] = $this->generateSku($productData['name'] ?? 'PRODUCT');
        }

        return Products::create($productData);
    }

    /**
     * Generate unique SKU
     * Format: PREFIX-RANDOM-TIMESTAMP
     * Example: PRD-AB12CD-1696320000
     */
    private function generateSku(string $productName): string
    {
        // Ambil 3 huruf pertama dari nama produk (uppercase)
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $productName), 0, 3));
        
        // Jika prefix kurang dari 3 karakter, isi dengan 'PRD'
        if (strlen($prefix) < 3) {
            $prefix = 'PRD';
        }

        // Generate random string 6 karakter
        $random = strtoupper(Str::random(6));

        // Timestamp
        $timestamp = time();

        // Gabungkan menjadi SKU
        $sku = "{$prefix}-{$random}-{$timestamp}";

        // Cek apakah SKU sudah ada (untuk memastikan unique)
        while (Products::where('sku', $sku)->exists()) {
            $random = strtoupper(Str::random(6));
            $sku = "{$prefix}-{$random}-{$timestamp}";
        }

        return $sku;
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
