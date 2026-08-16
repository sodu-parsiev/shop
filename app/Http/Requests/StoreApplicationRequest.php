<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'volume' => ['required', 'in:5000_10000,10000_25000,25000_plus'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Укажите контактное лицо.',
            'phone.required' => 'Укажите телефон.',
            'volume.required' => 'Выберите объём партии.',
            'volume.in' => 'Выберите объём партии из предложенных вариантов.',
        ];
    }
}
