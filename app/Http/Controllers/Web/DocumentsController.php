<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public publications: an overview and one page each for credit ratings, financial statements and annual reports.
 * Search and filters are a plain GET form (?q=&year=&kind=&sort=&page=).
 */
class DocumentsController extends Controller
{
    public function index(): View
    {
        $published = fn () => Publication::query()->published();
        $latestFinance = $published()->ofType('finance')->orderByDesc('year')->get();
        $latestYear = $latestFinance->max('year');

        return view('web.documents.index', [
            'counts' => $published()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type'),
            'latestAnnual' => $published()->ofType('annual')->orderByDesc('year')->first(),
            'covers' => $published()->ofType('annual')->whereNotNull('cover_image')->orderByDesc('year')->limit(3)->get(),
            'ratings' => collect(array_keys(config('publications.agencies')))->mapWithKeys(fn (string $agency) => [$agency => $published()->ofType('credit')->where('agency', $agency)->orderByDesc('year')->first()]),
            'financeYear' => $latestYear,
            'financeDone' => $latestFinance->where('year', $latestYear)->pluck('period')->all(),
        ]);
    }

    public function show(Request $request, string $locale, string $type): View
    {
        abort_unless(in_array($type, Publication::TYPES, true), 404);

        // Invalid values are ignored (public page): they fall back to the defaults
        $input = validator($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer'],
            'kind' => ['nullable', 'string', 'max:30'],
            'sort' => ['nullable', 'in:desc,asc'],
        ])->valid();
        $filters = [
            'q' => trim((string) ($input['q'] ?? '')),
            'year' => $input['year'] ?? null,
            'kind' => $input['kind'] ?? null,
            'sort' => $input['sort'] ?? 'desc',
        ];
        $direction = $filters['sort'] === 'asc' ? 'asc' : 'desc';

        $query = Publication::query()->published()->ofType($type)
            ->when($filters['year'], fn ($q, $year) => $q->where('year', $year))
            ->when($filters['kind'] && $type === 'credit', fn ($q) => $q->where('agency', $filters['kind']))
            ->when($filters['kind'] && $type === 'finance', fn ($q) => $q->where('period', $filters['kind']));

        foreach (preg_split('/\s+/u', $filters['q'], -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn ($q) => $q->whereRaw('cast(year as text) like ?', [$like])
                ->orWhereRaw('cast(year + ? as text) like ?', [config('publications.buddhist_offset'), $like])
                ->orWhere('agency', 'ilike', $like)
                ->orWhere('rating', 'ilike', $like));
        }

        $query->orderBy('year', $direction)
            ->orderByRaw("case when period = 'annual' then 4 when period ~ '^[0-9]+$' then cast(period as integer) else 0 end desc")
            ->orderBy('id', $direction);

        $items = $type === 'annual' ? $query->get() : $query->paginate((int) config('publications.per_page'))->withQueryString();

        return view('web.documents.show', [
            'type' => $type,
            'filters' => $filters,
            'items' => $items,
            'total' => $type === 'annual' ? $items->count() : $items->total(),
            'years' => Publication::query()->published()->ofType($type)->distinct()->orderByDesc('year')->pluck('year'),
            'latestAnnual' => Publication::query()->published()->ofType('annual')->orderByDesc('year')->first(),
            'latestFinance' => $type === 'finance' ? Publication::query()->published()->ofType('finance')->orderByDesc('year')->orderByRaw("case when period = 'annual' then 4 when period ~ '^[0-9]+$' then cast(period as integer) else 0 end desc")->first() : null,
            'ratings' => $type === 'credit' ? collect(array_keys(config('publications.agencies')))->mapWithKeys(fn (string $agency) => [$agency => Publication::query()->published()->ofType('credit')->where('agency', $agency)->orderByDesc('year')->first()]) : collect(),
        ]);
    }
}
