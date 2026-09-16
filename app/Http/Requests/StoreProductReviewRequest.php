<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:5', 'max:1500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'আপনার নাম লিখুন।',
            'rating.required' => 'একটি রেটিং নির্বাচন করুন।',
            'body.required' => 'আপনার অভিজ্ঞতা লিখুন।',
            'body.min' => 'রিভিউটি কমপক্ষে ৫ অক্ষরের হতে হবে।',
            'image.image' => 'শুধু ছবি আপলোড করা যাবে।',
            'image.mimes' => 'ছবিটি JPG, PNG অথবা WebP হতে হবে।',
            'image.max' => 'ছবির সাইজ সর্বোচ্চ ৩ MB হতে পারবে।',
        ];
    }
}
