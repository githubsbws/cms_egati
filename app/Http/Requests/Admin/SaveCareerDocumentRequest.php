<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class SaveCareerDocumentRequest extends FormRequest
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
            "labels.{$default}" => ['required', 'string', 'max:255'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:'.config('careers.max_document_kb')],
            'external_url' => ['nullable', 'url:http,https', 'max:1000'],
            'is_notice' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function ($validator) {
            if (! $this->hasFile('file') && blank($this->input('external_url'))) {
                $validator->errors()->add('file', __('admin/careers.doc_required'));
            }
        }];
    }
}
