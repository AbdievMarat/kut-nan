<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IngredientMovementRequest extends FormRequest
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
            'date' => [
                'required',
                'date'
            ],
            'products' => ['array'],
            'products.*.quantity_cart' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value !== null && fmod($value, 0.5) != 0) {
                        $fail('Количество тележек должно быть кратно 0.5');
                    }
                }
            ],
            'products.*.quantity_total' => ['nullable', 'integer', 'min:0'],
            'ingredients' => ['array'],
            'ingredients.*.income' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.usage_missing' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.usage_taken_from_stock' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.usage_kitchen' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasProductData = false;
            $hasIngredientData = false;

            // Проверяем есть ли данные в products
            if ($this->has('products')) {
                foreach ($this->input('products', []) as $product) {
                    if (!empty($product['quantity_cart'])) {
                        $hasProductData = true;
                        break;
                    }
                }
            }

            // Проверяем есть ли данные в ingredients
            if ($this->has('ingredients')) {
                foreach ($this->input('ingredients', []) as $ingredient) {
                    if (!empty($ingredient['income']) ||
                        !empty($ingredient['usage_missing']) ||
                        !empty($ingredient['usage_taken_from_stock']) ||
                        !empty($ingredient['usage_kitchen'])) {
                        $hasIngredientData = true;
                        break;
                    }
                }
            }

            // Если нет данных ни в одном массиве, добавляем ошибку
            if (!$hasProductData && !$hasIngredientData) {
                $validator->errors()->add('general', 'Необходимо заполнить хотя бы одно поле в разделе "Произведенная продукция" или "Приход и расход сырья"');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Поле "Дата" обязательно для заполнения.',
            'date.date' => 'Поле "Дата" должно быть корректной датой.',
            'products.array' => 'Продукты должны быть массивом.',
            'products.*.quantity_cart.numeric' => 'Количество тележек должно быть числом.',
            'products.*.quantity_cart.min' => 'Количество тележек не может быть отрицательным.',
            'ingredients.array' => 'Ингредиенты должны быть массивом.',
            'ingredients.*.income.numeric' => 'Приход ингредиента должен быть числом.',
            'ingredients.*.income.min' => 'Приход ингредиента не может быть отрицательным.',
            'ingredients.*.usage_missing.numeric' => 'Расход "не хватает" должен быть числом.',
            'ingredients.*.usage_missing.min' => 'Расход "не хватает" не может быть отрицательным.',
            'ingredients.*.usage_taken_from_stock.numeric' => 'Расход "забрали со склада" должен быть числом.',
            'ingredients.*.usage_taken_from_stock.min' => 'Расход "забрали со склада" не может быть отрицательным.',
            'ingredients.*.usage_kitchen.numeric' => 'Расход "кухня" должен быть числом.',
            'ingredients.*.usage_kitchen.min' => 'Расход "кухня" не может быть отрицательным.',
        ];
    }
}
