<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarkdownRequest extends FormRequest
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
            'id' => ['required', 'numeric', Rule::exists('markdowns', 'id')],
            'item_amounts' => ['required', 'array'],
            'item_amounts.*' => ['nullable', 'numeric', 'min:0'],
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
