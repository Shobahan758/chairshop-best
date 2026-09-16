<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
        return ['name' => 'required|string|max:100', 'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'], 'email' => 'nullable|email', 'district' => 'required|string|max:100', 'area' => 'required|string', 'address' => 'required|string|min:10', 'payment_method' => 'required|in:cod,bkash,nagad', 'payment_phone' => ['exclude_if:payment_method,cod', 'required', 'regex:/^01[3-9][0-9]{8}$/'], 'transaction_id' => ['exclude_if:payment_method,cod', 'required', 'string', 'regex:/^[A-Za-z0-9-]+$/', 'max:100']];
    }

    public function messages(): array
    {
        return ['name.required' => 'আপনার নাম লিখুন', 'phone.required' => 'মোবাইল নম্বর লিখুন', 'phone.regex' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন', 'district.required' => 'জেলার নাম লিখুন', 'area.required' => 'এলাকা লিখুন', 'address.required' => 'সম্পূর্ণ ঠিকানা লিখুন', 'payment_phone.required' => 'যে নম্বর থেকে টাকা পাঠিয়েছেন সেটি লিখুন', 'payment_phone.regex' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন', 'transaction_id.required' => 'Transaction ID লিখুন', 'transaction_id.regex' => 'সঠিক Transaction ID লিখুন'];
    }
}
