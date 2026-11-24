<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class SaveHomeAgencyRequest extends FormRequest
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
            "translations.{$default}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'logo' => [
                $this->route('agency') ? 'nullable' : 'required',
                'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:'.config('home_content.max_logo_kb'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    // An SVG is shown through <img>, but never accept one that carries script
                    if ($value instanceof UploadedFile && strtolower($value->getClientOriginalExtension()) === 'svg'
                        && preg_match('/<script|\son\w+\s*=|javascript:/i', (string) file_get_contents($value->getRealPath()))) {
                        $fail(__('admin/home_content.svg_unsafe'));
                    }
                },
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
