<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add or edit a website language. Access is limited by the menu.permission:5 route middleware.
 * The code (th, en, fr ...) is fixed after creation because it is used in URLs and file names.
 */
class SaveLanguageRequest extends FormRequest
{
    /** Codes that would collide with fixed website paths. */
    private const RESERVED = ['api', 'css', 'js', 'img', 'web'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creating = $this->route('language') === null;

        return [
            'code' => $creating
                ? ['required', 'regex:/^[a-z]{2,3}$/', Rule::notIn(self::RESERVED), Rule::unique('languages', 'code')]
                : ['prohibited'],
            'name' => ['required', 'string', 'max:100'],
            'native_name' => ['required', 'string', 'max:100'],
            'direction' => ['required', Rule::in(['ltr', 'rtl'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'flag' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_flag' => ['nullable', 'boolean'],
            'json_file' => ['nullable', 'file', 'mimetypes:application/json,text/plain', 'max:1024'],
        ];
    }
}
