<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Create/edit a news category. The default language name is required, others are optional.
 */
class SaveNewsCategoryRequest extends FormRequest
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
        $defaultLocale = Language::active()->where('is_default', 'y')->value('code');

        return [
            "names.{$defaultLocale}" => ['required', 'string', 'max:255'],
            'names.*' => ['nullable', 'string', 'max:255'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
