<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        $fields = config('site_content.'.$this->route('page').'.'.$this->route('section'));
        abort_unless(is_array($fields), 404);
        $rules = ['content' => ['required', 'array:'.implode(',', array_keys($fields))]];

        foreach ($fields as $key => $field) {
            $rules['content.'.$key] = ['present', 'nullable', 'string', 'max:10000'];
            if (in_array($field['type'], ['image', 'link'], true)) {
                $rules['content.'.$key] = [($field['optional'] ?? false) ? 'nullable' : 'required', 'string', 'max:2048', 'regex:~^(?:https?://[^\s<>"\x27\\\\]+|/(?!/)[^\s<>"\x27\\\\]*|\#[a-zA-Z][a-zA-Z0-9_-]*)$~'];
            }
            if ($field['type'] === 'email') {
                $rules['content.'.$key] = ['present', 'nullable', 'email', 'max:255'];
            }
            if ($field['type'] === 'phone') {
                $rules['content.'.$key] = ['present', 'nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()-]{6,30}$/'];
            }
            if ($field['type'] === 'image') {
                $rules['uploads.'.$key] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            }
        }

        $imageKeys = array_keys(array_filter($fields, fn (array $field): bool => $field['type'] === 'image'));
        $rules['uploads'] = $imageKeys ? ['sometimes', 'array:'.implode(',', $imageKeys)] : ['prohibited'];

        return $rules;
    }
}
