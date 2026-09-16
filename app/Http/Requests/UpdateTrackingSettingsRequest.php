<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTrackingSettingsRequest extends FormRequest
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
            'meta_pixel_id' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'google_tag_id' => ['nullable', 'string', 'max:50', 'regex:/^(G|GT|GTM|AW)-[A-Za-z0-9-]+$/'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'pinterest_tag_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'snapchat_pixel_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'tracking_enabled' => ['nullable', 'boolean'],
        ];
    }
}
