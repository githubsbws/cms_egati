<?php

namespace App\Http\Requests\Admin;

use App\Models\CareerJob;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCareerJobRequest extends FormRequest
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
        /** @var CareerJob|null $job */
        $job = $this->route('job');

        return [
            "translations.{$default}.title" => ['required', 'string', 'max:255'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.department' => ['nullable', 'string', 'max:255'],
            'translations.*.qualifications' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists('career_categories', 'id')->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $job?->category_id))],
            'vacancies' => ['required', 'integer', 'min:1', 'max:999'],
            'start_on' => ['required', 'date'],
            'deadline_on' => ['required', 'date', 'after_or_equal:start_on'],
            'updated_on' => ['required', 'date'],
            'stage' => ['required', Rule::in(CareerJob::STAGES)],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
