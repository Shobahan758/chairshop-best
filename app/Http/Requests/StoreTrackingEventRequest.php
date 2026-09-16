<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingEventRequest extends FormRequest
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
            'event' => ['required', 'in:page_view,landing_page_view,product_view,button_click,add_to_cart,checkout_open,form_submit,purchase_complete,phone_whatsapp_click,scroll_engagement'],
            'path' => ['required', 'string', 'max:500'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
