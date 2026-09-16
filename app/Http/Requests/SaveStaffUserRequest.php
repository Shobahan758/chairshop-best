<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['permissions' => $this->input('permissions', [])]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'distinct', Rule::in(array_keys(config('staff_permissions')))],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager'])],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
