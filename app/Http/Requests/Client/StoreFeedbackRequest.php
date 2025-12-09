<?php

namespace App\Http\Requests\Client;

use App\Models\Feedback;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
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
        $isAnonymous = $this->boolean('is_anonymous');

        return [
            'is_anonymous' => ['required', 'boolean'],
            'full_name' => $isAnonymous ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'phone' => $isAnonymous ? ['nullable', 'string', 'max:20'] : ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $isAnonymous = $this->boolean('is_anonymous');
            $fullName = $this->input('full_name');
            $phone = $this->input('phone');
            $message = $this->input('message');

            if (!$message) {
                return; // Если сообщение пустое, валидация на required сработает
            }

            $query = Feedback::where('message', $message);

            if (!$isAnonymous && $fullName && $phone) {
                // Для неанонимных записей проверяем комбинацию full_name, phone, message
                $exists = $query->where('full_name', $fullName)
                      ->where('phone', $phone)
                      ->where('is_anonymous', false)
                      ->exists();

                if ($exists) {
                    $validator->errors()->add('message', 'Такая же обратная связь уже существует.');
                }
            } else {
                // Для анонимных записей проверяем только message (если оно точно такое же)
                $exists = $query->where('is_anonymous', true)
                      ->whereNull('full_name')
                      ->whereNull('phone')
                      ->exists();

                if ($exists) {
                    $validator->errors()->add('message', 'Такое же сообщение уже было отправлено.');
                }
            }
        });
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'is_anonymous.required' => 'Пожалуйста, укажите, хотите ли вы оставить обратную связь анонимно',
            'full_name.required' => 'Пожалуйста, укажите ваше ФИО',
            'phone.required' => 'Пожалуйста, укажите ваш номер телефона',
            'message.required' => 'Пожалуйста, напишите ваше сообщение',
            'message.min' => 'Сообщение должно содержать минимум 10 символов',
            'message.max' => 'Сообщение не должно превышать 5000 символов',
        ];
    }
}
