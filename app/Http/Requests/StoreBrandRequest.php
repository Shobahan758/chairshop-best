<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
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
            'slug' => ['string', 'alpha_dash', 'max:255', Rule::unique('brands', 'slug')],
            'logo' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Brand name is required.', 'slug.required' => 'Brand slug is required.', 'slug.unique' => 'This brand slug is already in use.', 'logo.url' => 'Logo must be a valid http or https URL.'];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::of($this->string('name')->toString())->lower()->replaceMatches('/[^\pL\pM\pN]+/u', '-')->trim('-')->toString()]);
        }
    }
}
