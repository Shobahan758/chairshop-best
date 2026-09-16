<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'subcategory_id' => ['nullable', 'integer', Rule::exists('subcategories', 'id')->where('category_id', $this->integer('category_id'))],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['string', 'alpha_dash', 'max:255', Rule::unique('products', 'slug')],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'additional_images' => ['nullable', 'array'],
            'additional_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'material' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'just_for_you' => ['nullable', 'boolean'],
            'office_essential' => ['nullable', 'boolean'],
            'gaming_pick' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $attributes = [
            'featured' => $this->boolean('featured'),
            'just_for_you' => $this->boolean('just_for_you'),
            'office_essential' => $this->boolean('office_essential'),
            'gaming_pick' => $this->boolean('gaming_pick'),
        ];

        if (! $this->filled('short_description') && $this->filled('name')) {
            $attributes['short_description'] = $this->string('name')->toString();
        }

        if (! $this->filled('slug') && $this->filled('name')) {
            $attributes['slug'] = Str::of($this->string('name')->toString())->lower()->replaceMatches('/[^\pL\pM\pN]+/u', '-')->trim('-')->toString();
        }

        if ($this->filled('discount_percentage') && is_numeric($this->input('price')) && is_numeric($this->input('discount_percentage'))) {
            $price = (float) $this->input('price');
            $discountPercentage = (float) $this->input('discount_percentage');

            if ($discountPercentage === 0.0) {
                $attributes['sale_price'] = null;
            } elseif ($discountPercentage > 0 && $discountPercentage <= 100) {
                $attributes['sale_price'] = round($price * (1 - ($discountPercentage / 100)), 2);
            }
        }

        $this->merge($attributes);
    }
}
