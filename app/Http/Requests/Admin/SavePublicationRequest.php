<?php

namespace App\Http\Requests\Admin;

use App\Models\Publication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One document of a type (credit | finance | annual) taken from the route default "type".
 */
class SavePublicationRequest extends FormRequest
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
        $type = (string) $this->route('type');
        abort_unless(in_array($type, Publication::TYPES, true), 404);
        $pdf = ['nullable', 'file', 'mimes:pdf', 'max:'.config('publications.max_pdf_kb')];
        $url = ['nullable', 'url:http,https', 'max:1000'];

        $rules = [
            'year' => ['required', 'integer', 'between:1990,2200'],
            'file' => $pdf,
            'external_url' => $url,
            'remove_file' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ];

        if ($type === 'credit') {
            $rules['agency'] = ['required', Rule::in(array_keys(config('publications.agencies')))];
            $rules['rating'] = ['required', 'string', 'max:20'];
        }
        if ($type === 'finance') {
            $rules['period'] = ['required', Rule::in(Publication::PERIODS)];
            $rules['file_en'] = $pdf;
            $rules['external_url_en'] = $url;
            $rules['remove_file_en'] = ['nullable', 'boolean'];
        }
        if ($type === 'annual') {
            $rules['cover'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('publications.max_cover_kb')];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['year.between' => __('admin/documents.year_out_of_range')];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function ($validator) {
            $type = (string) $this->route('type');
            /** @var Publication|null $current */
            $current = $this->route('publication');

            // A document needs a file or a link (the existing one counts when editing)
            $hasThai = $this->hasFile('file') || filled($this->input('external_url')) || ($current && ! $this->boolean('remove_file') && ($current->file_path || $current->external_url));
            $hasEnglish = $type === 'finance' && ($this->hasFile('file_en') || filled($this->input('external_url_en')) || ($current && ! $this->boolean('remove_file_en') && ($current->file_path_en || $current->external_url_en)));
            if (! $hasThai && ! $hasEnglish) {
                $validator->errors()->add('file', __('admin/documents.file_required'));
            }

            // One document per agency+year (credit), period+year (finance) or year (annual)
            $duplicate = Publication::query()->ofType($type)->where('year', (int) $this->input('year'))
                ->when($type === 'credit', fn ($q) => $q->where('agency', $this->input('agency')))
                ->when($type === 'finance', fn ($q) => $q->where('period', $this->input('period')))
                ->when($current, fn ($q) => $q->whereKeyNot($current->id))
                ->exists();
            if ($duplicate && ! $validator->errors()->has('year')) {
                $validator->errors()->add('year', __('admin/documents.duplicate'));
            }
        }];
    }
}
