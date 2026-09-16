<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['string', 'alpha_dash', 'max:255', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::of($this->string('name')->toString())->lower()->replaceMatches('/[^\pL\pM\pN]+/u', '-')->trim('-')->toString()]);
        }
    }
}
