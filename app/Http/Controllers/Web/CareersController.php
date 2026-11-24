<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CareerCategory;
use App\Models\CareerJob;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Public careers pages: the list of recruitment notices with filters, and one notice with its documents.
 * Filtering is a plain GET form (?q=&category=&status=). Statuses come from the dates, so they are worked out here and not in SQL.
 */
class CareersController extends Controller
{
    private const STATUSES = ['open', 'assessment', 'results', 'closed'];

    public function index(Request $request): View
    {
        // Invalid values are ignored (public page): they fall back to the defaults
        $input = validator($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:'.implode(',', self::STATUSES)],
        ])->valid();
        $filters = [
            'q' => trim((string) ($input['q'] ?? '')),
            'category' => $input['category'] ?? null,
            'status' => $input['status'] ?? 'all',
        ];

        $locale = app()->getLocale();
        $defaultLocale = Language::active()->where('is_default', 'y')->value('code');
        $now = now('Asia/Bangkok');

        /** @var Collection<int, CareerJob> $all */
        $all = CareerJob::query()->published()->with(['translations', 'category'])->get()
            ->each(fn (CareerJob $job) => $job->setAttribute('current_status', $job->status($now)));

        $terms = preg_split('/\s+/u', mb_strtolower($filters['q']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $jobs = $all
            ->filter(fn (CareerJob $job) => $filters['status'] === 'all' || $job->current_status === $filters['status'])
            ->filter(fn (CareerJob $job) => ! $filters['category'] || $job->category_id === (int) $filters['category'])
            ->filter(function (CareerJob $job) use ($terms) {
                $haystack = mb_strtolower($job->translations->map(fn ($t) => $t->title.' '.$t->department)->implode(' ').' '.$job->category?->name.' '.$job->category?->name_en);

                return collect($terms)->every(fn ($term) => str_contains($haystack, $term));
            })
            ->sortBy([
                fn (CareerJob $a, CareerJob $b) => CareerJob::STATUS_ORDER[$a->current_status] <=> CareerJob::STATUS_ORDER[$b->current_status],
                fn (CareerJob $a, CareerJob $b) => $b->updated_on <=> $a->updated_on,
            ])->values();

        return view('web.careers.index', [
            'filters' => $filters,
            'jobs' => $jobs,
            'total' => $all->count(),
            'counts' => $all->countBy('current_status'),
            'openCount' => $all->where('current_status', 'open')->count(),
            'reviewed' => $all->max('updated_on'),
            'categories' => CareerCategory::active()->whereHas('jobs', fn ($q) => $q->published())->orderBy('sort_order')->orderBy('id')->get(),
            'statuses' => self::STATUSES,
            'defaultLocale' => $defaultLocale,
            'locale' => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $job = CareerJob::query()->published()->where('slug', $slug)->with(['translations', 'category', 'documents'])->firstOrFail();

        return view('web.careers.show', [
            'job' => $job,
            'status' => $job->status(),
            'defaultLocale' => Language::active()->where('is_default', 'y')->value('code'),
            'locale' => $locale,
        ]);
    }
}
