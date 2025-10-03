<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'order_number' => ['sometimes', 'string', 'max:100', 'unique:orders,order_number'],
            'order_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:pending,completed,cancelled'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'is_paid' => ['sometimes', 'boolean'],
            
            // Order items (array of items)
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
