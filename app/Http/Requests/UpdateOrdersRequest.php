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
        $routeParam = request()->route('order');
        $orderId = $routeParam instanceof Orders ? $routeParam->getKey() : $routeParam;

        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'order_number' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('orders', 'order_number')->ignore($orderId),
            ],
            'order_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:pending,completed,cancelled'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'is_paid' => ['sometimes', 'boolean'],
        ];
    }
}
