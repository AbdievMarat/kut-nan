<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusRequest extends FormRequest
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
                Rule::unique('buses', 'license_plate')->ignore($this->route('bus')->id)
            ],
            'serial_number' => ['required'],
            'sort' => ['required', 'numeric'],
            'is_active' => ['required'],
        ];
    }
}
