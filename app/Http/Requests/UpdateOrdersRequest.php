<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Orders;

class UpdateOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Default param dari apiResource('orders', ...) adalah 'order' (singular)
        $routeParam = request()->route('order'); // ganti ke 'orders' jika memang param-mu plural
        $orderId = $routeParam instanceof Orders ? $routeParam->getKey() : $routeParam;

        return [
            'customer_id'  => ['sometimes', 'nullable', 'exists:customers,id'],
            'order_number' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('orders', 'order_number')->ignore($orderId),
            ],
            'user_id'      => ['sometimes', 'nullable', 'exists:users,id'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
