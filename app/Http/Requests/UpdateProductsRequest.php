<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Products;

class UpdateProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Ambil parameter {product} dari route apiResource('products', ...)
        $routeParam = request()->route('product'); // <— tidak pakai $this->route()

        // Bisa berupa model (jika pakai route-model-binding) atau ID (int/string)
        $productId = $routeParam instanceof Products ? $routeParam->getKey()
                 : (is_numeric($routeParam) ? (int) $routeParam : null);

        return [
            // pakai 'sometimes' agar PATCH boleh kirim sebagian field saja
            'name'      => ['sometimes', 'string', 'max:255'],
            'price'     => ['sometimes', 'numeric', 'min:0'],
            'stock'     => ['sometimes', 'integer', 'min:0'],
            'sku'       => [
                'sometimes', 'string', 'max:100',
                Rule::unique('products', 'sku')->ignore($productId, 'id'),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
