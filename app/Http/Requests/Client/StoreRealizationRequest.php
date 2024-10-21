<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRealizationRequest extends FormRequest
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
            'realization_id' => [
                'required',
                'numeric',
                Rule::exists('realizations', 'id')
            ],
            'realization_shop_id' => ['required', 'array'],
            'shop' => ['required', 'array'],
            'shop.*' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'array'],
            'amount.*' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'shop.*.required' => 'Название магазина обязательно для заполнения',
            'shop.*.max' => 'Название магазина не должно превышать 500 символов',
            'amount.*.required' => 'Сумма обязательна для заполнения',
            'amount.*.numeric' => 'Сумма должна быть числовой',
            'amount.*.min' => 'Сумма должна быть не меньше 0',
        ];
    }
}
