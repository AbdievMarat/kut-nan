<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIngredientRequest extends FormRequest
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
        $ingredientId = $this->route('ingredient') ? $this->route('ingredient')->id : $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ingredients', 'name')->ignore($ingredientId)
            ],
            'short_name' => [
                'required',
                'string',
                'max:255',
            ],
            'unit' => [
                'required',
                'string',
                'max:255',
            ],
            'price' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],
            'sort' => [
                'required',
                'integer',
                'min:0',
                'max:1000',
            ],
            'is_active' => ['required'],
        ];
    }
}
