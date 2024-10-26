<?php

namespace App\Http\Requests\Client;

use App\Models\Bus;
use App\Models\Order;
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
                Rule::exists('buses', 'license_plate')->where('is_active', Bus::IS_ACTIVE)
            ],
            'type_operation' => [
                'required',
                'numeric',
                Rule::in([Order::TYPE_OPERATION_ORDER, Order::TYPE_OPERATION_REALIZATION, Order::TYPE_OPERATION_REMAINDER, Order::TYPE_OPERATION_MARKDOWN])
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
