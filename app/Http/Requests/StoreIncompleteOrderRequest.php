<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncompleteOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'phone' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'], 'email' => ['nullable', 'email', 'max:255'], 'district' => ['nullable', 'string', 'max:100'], 'area' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:1000']];
    }
}
