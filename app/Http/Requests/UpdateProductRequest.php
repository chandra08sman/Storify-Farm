<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'numeric', 'gt:0'],
            'temperature' => ['nullable', 'string', 'max:50'],
            'humidity' => ['nullable', 'string', 'max:50'],
        ];
    }
}