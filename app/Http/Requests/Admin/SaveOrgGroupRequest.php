<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add or edit a committee (or rename the board / management group).
 */
class SaveOrgGroupRequest extends FormRequest
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
            "names.{$default}" => ['required', 'string', 'max:255'],
            'names.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_document' => ['nullable', 'boolean'],
        ];
    }
}
