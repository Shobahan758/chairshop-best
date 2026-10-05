<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubcategoryRequest extends FormRequest
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
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['string', 'alpha_dash', 'max:255', Rule::unique('subcategories', 'slug')->ignore($this->route('subcategory'))],
        ];
    }

    public function messages(): array
    {
        return ['category_id.required' => 'Parent category is required.', 'category_id.exists' => 'Select a valid parent category.', 'name.required' => 'Sub category name is required.', 'slug.required' => 'Sub category slug is required.', 'slug.unique' => 'This sub category slug is already in use.'];
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('category_id')) {
                return;
            }

            $subcategory = $this->route('subcategory');
            if ($this->integer('category_id') !== $subcategory->category_id && $subcategory->products()->exists()) {
                $validator->errors()->add('category_id', 'Move this sub category’s products before changing its parent category.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::of($this->string('name')->toString())->lower()->replaceMatches('/[^\pL\pM\pN]+/u', '-')->trim('-')->toString()]);
        }
    }
}
