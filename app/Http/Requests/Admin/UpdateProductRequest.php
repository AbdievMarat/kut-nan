<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', Rule::unique('products', 'name')->ignore($this->route('product')->id)],
            'sort' => ['required', 'numeric'],
            'pieces_per_cart' => ['required', 'numeric', 'min:1', 'max:2000'],
            'is_active' => ['required'],
            'is_in_report' => ['required'],
        ];
    }
}
