<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create/edit news. Access is already limited by the menu.permission:8 route middleware.
 * Only the default language must have a title; other languages are optional.
 */
class SaveNewsRequest extends FormRequest
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
        $image = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        $defaultLocale = Language::active()->where('is_default', 'y')->value('code');

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'cover' => ['nullable', ...$image],
            'remove_cover' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => $image,
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:500'],
            'translations.*.title' => ['nullable', 'string', 'max:500'],
            'translations.*.slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.content' => ['nullable', 'string', 'max:200000'],
        ];
    }
}
