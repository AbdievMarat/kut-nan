<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductIngredientsRequest extends FormRequest
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
            'ingredient_formula' => ['array'],
            'ingredient_formula.*' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // 1. Только разрешённые символы и переменные
                    if (!preg_match('/^\s*[\d\.\+\-\*\/\(\)\s\$quantity\$portion]+\s*$/', $value)) {
                        $fail('Формула содержит недопустимые символы. Разрешены только цифры, операторы и переменные: $quantity, $portion.');
                        return;
                    }

                    // 2. Пробное выполнение
                    $quantity = 1000;
                    $portion = 216;

                    $formulaStr = str_replace('$quantity', $quantity, $value);
                    $formulaStr = str_replace('$portion', $portion, $formulaStr);

                    try {
                        $result = eval('return ' . $formulaStr . ';');
                        if (!is_numeric($result)) {
                            $fail('Формула не возвращает числовое значение.');
                        }
                    } catch (\Throwable $e) {
                        $fail('Ошибка при вычислении формулы: ' . $e->getMessage());
                    }
                },
            ],
        ];
    }
}
