<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicensePlateRequest extends FormRequest
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
            'license_plate' => [
                'required',
                'numeric',
                Rule::exists('buses', 'license_plate')
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'license_plate.required' => 'Номер машины обязателен для ввода',
            'license_plate.numeric' => 'Номер машины должен быть числовым значением',
            'license_plate.exists' => 'Номер машины не найден',
        ];
    }
}
