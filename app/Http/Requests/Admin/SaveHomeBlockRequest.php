<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Saves every block of one home section (hero, story, presentation). The blocks and their fields come from config/home_content.php.
 */
class SaveHomeBlockRequest extends FormRequest
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
        $section = config('home_content.sections.'.$this->route('section')) ?? abort(404);
        $rules = [];

        foreach ($section['blocks'] as $code => $block) {
            $rules["blocks.{$code}.video"] = ['nullable', 'file', 'mimes:mp4,webm', 'max:'.config('home_content.max_video_kb')];
            $rules["blocks.{$code}.poster"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('home_content.max_image_kb')];

            foreach ($block['texts'] as $field => $meta) {
                if (($meta['admin'] ?? true) === false) {
                    continue;
                }
                $rules["blocks.{$code}.texts.*.{$field}"] = ['nullable', 'string', 'max:'.(($meta['long'] ?? false) ? 2000 : 255)];
            }
            foreach ($block['plain'] ?? [] as $field => $meta) {
                if (($meta['admin'] ?? true) === false) {
                    continue;
                }
                $rules["blocks.{$code}.plain.{$field}"] = $meta['type'] === 'duration'
                    ? ['nullable', 'regex:/^(\d{1,2}:)?[0-5]?\d:[0-5]\d$/']
                    : ['nullable', 'string', 'max:500', 'regex:#^(https?://\S+|/?[A-Za-z0-9][A-Za-z0-9/_\-.\#?=&]*)$#'];
            }
        }

        return $rules;
    }
}
