<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'id' => ['required', 'numeric', Rule::exists('orders', 'id')],
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['required', 'integer', Rule::exists('order_items', 'id')],
            'item_amounts' => ['required', 'array'],
            'item_amounts.*' => ['nullable', 'numeric', 'min:0'],
            'product_1' => ['nullable', 'numeric'],
            'product_2' => ['nullable', 'numeric'],
            'product_3' => ['nullable', 'numeric'],
            'product_4' => ['nullable', 'numeric'],
            'product_5' => ['nullable', 'numeric'],
            'product_6' => ['nullable', 'numeric'],
            'product_7' => ['nullable', 'numeric'],
            'product_8' => ['nullable', 'numeric'],
            'product_9' => ['nullable', 'numeric'],
            'product_10' => ['nullable', 'numeric'],
            'product_11' => ['nullable', 'numeric'],
            'product_12' => ['nullable', 'numeric'],
            'product_13' => ['nullable', 'numeric'],
            'product_14' => ['nullable', 'numeric'],
            'product_15' => ['nullable', 'numeric'],
            'product_16' => ['nullable', 'numeric'],
            'product_17' => ['nullable', 'numeric'],
            'product_18' => ['nullable', 'numeric'],
            'product_19' => ['nullable', 'numeric'],
            'product_20' => ['nullable', 'numeric'],
            'product_21' => ['nullable', 'numeric'],
            'product_22' => ['nullable', 'numeric'],
            'product_23' => ['nullable', 'numeric'],
            'product_24' => ['nullable', 'numeric'],
            'product_25' => ['nullable', 'numeric'],
            'product_26' => ['nullable', 'numeric'],
            'product_27' => ['nullable', 'numeric'],
            'product_28' => ['nullable', 'numeric'],
            'product_29' => ['nullable', 'numeric'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'item_amounts.*.numeric' => 'Сумма должна быть числовой',
            'item_amounts.*.min' => 'Сумма должна быть не меньше 0',
        ];
    }
}
