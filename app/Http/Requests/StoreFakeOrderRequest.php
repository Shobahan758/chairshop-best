<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFakeOrderRequest extends FormRequest
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
        return ['name' => ['required', 'string', 'max:100'], 'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'], 'email' => ['nullable', 'email'], 'district' => ['required', 'string', 'max:100'], 'area' => ['required', 'string', 'max:255'], 'address' => ['required', 'string', 'max:1000'], 'product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1', 'max:100'], 'status' => ['required', 'in:অর্ডার গ্রহণ,নিশ্চিত,প্রস্তুত,পাঠানো,ডেলিভারি সম্পন্ন,বাতিল']];
    }
}
