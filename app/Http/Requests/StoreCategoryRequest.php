<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'slug' => ['string', 'alpha_dash', 'max:255', Rule::unique('categories', 'slug')],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'slug.required' => 'Category slug is required.',
            'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique' => 'This category slug is already in use.',
            'icon.regex' => 'Use a valid Bootstrap icon name, such as chair or grid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::of($this->string('name')->toString())->lower()->replaceMatches('/[^\pL\pM\pN]+/u', '-')->trim('-')->toString()]);
        }
    }
}
