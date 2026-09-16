<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'bkash_number' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'nagad_number' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'currency_code' => ['required', 'string', 'size:3'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'timezone:all'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
