<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCareerJobRequest;
use App\Models\CareerCategory;
use App\Models\CareerJob;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Recruitment notices (admin menu id 14). Documents are added on the edit page.
 */
class CareerJobController extends Controller
{
    public function index(): View
    {
        return view('admin.careers.jobs', [
            'jobs' => CareerJob::query()->with(['translations', 'category'])->withCount('documents')->orderByDesc('updated_on')->orderByDesc('id')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new CareerJob(['vacancies' => 1, 'stage' => 'application', 'is_published' => true, 'start_on' => today(), 'deadline_on' => today()->addMonth(), 'updated_on' => today()]));
    }

    public function store(SaveCareerJobRequest $request): RedirectResponse
    {
        $job = DB::transaction(fn () => $this->save($request, new CareerJob));

        return redirect()->route('admin.careers.jobs.edit', $job)->with('success', __('admin/careers.saved'));
    }

    public function edit(CareerJob $job): View
    {
        return $this->form($job->load(['translations', 'documents']));
    }

    public function update(SaveCareerJobRequest $request, CareerJob $job): RedirectResponse
    {
        DB::transaction(fn () => $this->save($request, $job));

        return redirect()->route('admin.careers.jobs.edit', $job)->with('success', __('admin/careers.saved'));
    }

    public function destroy(CareerJob $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('admin.careers.jobs.index')->with('success', __('admin/careers.deleted'));
    }

    protected function defaultLocale(): ?string
    {
        return Language::active()->where('is_default', 'y')->value('code');
    }

    protected function form(CareerJob $job): View
    {
        return view('admin.careers.job-form', [
            'job' => $job,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
            'categories' => CareerCategory::query()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $job->category_id))->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    protected function save(SaveCareerJobRequest $request, CareerJob $job): CareerJob
    {
        $data = $request->validated();
        $defaultLocale = $this->defaultLocale();
        $job->fill([
            'category_id' => $data['category_id'],
            'vacancies' => $data['vacancies'],
            'start_on' => $data['start_on'],
            'deadline_on' => $data['deadline_on'],
            'updated_on' => $data['updated_on'],
            'stage' => $data['stage'],
            'is_published' => $request->boolean('is_published'),
            'updated_by' => Auth::id(),
        ]);
        if (! $job->exists) {
            $job->slug = $this->newSlug((string) ($data['translations']['en']['title'] ?? $data['translations'][$defaultLocale]['title'] ?? ''));
        }
        $job->save();

        foreach (Language::active()->pluck('code') as $locale) {
            $row = $data['translations'][$locale] ?? [];
            $title = trim(strip_tags((string) ($row['title'] ?? '')));
            if ($title === '') {
                $job->translations()->where('locale', $locale)->delete();

                continue;
            }
            $qualifications = collect(preg_split('/\R/u', (string) ($row['qualifications'] ?? '')) ?: [])
                ->map(fn ($line) => trim(strip_tags($line)))->filter()->values()->all();
            $job->translations()->updateOrCreate(['locale' => $locale], [
                'title' => $title,
                'department' => trim(strip_tags((string) ($row['department'] ?? ''))) ?: null,
                'qualifications' => $qualifications,
            ]);
        }

        return $job;
    }

    /**
     * A readable, unique address part such as "senior-legal-officer-ab12".
     */
    protected function newSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 60, '')) ?: 'position';

        do {
            $slug = $base.'-'.Str::lower(Str::random(4));
        } while (CareerJob::withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }
}
