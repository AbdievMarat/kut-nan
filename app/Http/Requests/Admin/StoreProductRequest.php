<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'name' => ['required', Rule::unique('products', 'name')],
            'sort' => ['required', 'numeric'],
            'pieces_per_cart' => ['required', 'numeric', 'min:1', 'max:2000'],
            'production_cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'is_in_report' => ['required'],
        ];
    }
}
