<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add or edit a person in the board / committees / management team.
 * The name and position are required in the default language only.
 */
class SaveOrgMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Language::active()->where('is_default', 'y')->value('code');

        return [
            'group_id' => ['required', 'integer', 'exists:org_groups,id'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'remove_photo' => ['nullable', 'boolean'],
            'translations' => ['required', 'array'],
            "translations.{$default}.name" => ['required', 'string', 'max:255'],
            "translations.{$default}.role" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.role' => ['nullable', 'string', 'max:255'],
        ];
    }
}
