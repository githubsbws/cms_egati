<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public procurement page: category tiles, search + filters, results with details.
 * Everything is a normal GET form (?category=&q=&year=&method=&sort=&files=&size=&page=), so no script is needed to filter.
 */
class ProcurementController extends Controller
{
    private const CATEGORIES = ['plan', 'price', 'notice', 'winner_in', 'winner_out'];

    public function index(Request $request): View
    {
        // Invalid values are ignored (public page): they fall back to the defaults
        $input = validator($request->query(), [
            'category' => ['nullable', 'in:all,'.implode(',', self::CATEGORIES)],
            'q' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer'],
            'method' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:new,old,title,high,low'],
            'files' => ['nullable', 'boolean'],
            'size' => ['nullable', 'in:10,20,50'],
        ])->valid();

        $filters = [
            'category' => $input['category'] ?? 'plan',
            'q' => trim((string) ($input['q'] ?? '')),
            'year' => $input['year'] ?? null,
            'method' => $input['method'] ?? null,
            'sort' => $input['sort'] ?? 'new',
            'files' => (bool) ($input['files'] ?? false),
            'size' => (int) ($input['size'] ?? 10),
        ];

        $query = ProcurementRecord::query()->published()
            ->with(['vendor', 'agency', 'workType', 'method', 'attachments'])
            ->when($filters['category'] !== 'all', fn ($q) => $q->where('type', $filters['category']))
            ->when($filters['year'], fn ($q, $year) => $q->where('fiscal_year', $year))
            ->when($filters['method'], fn ($q, $id) => $q->where('method_id', $id))
            ->when($filters['files'], fn ($q) => $q->whereHas('attachments'));

        foreach (preg_split('/\s+/u', $filters['q'], -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn ($q) => $q->where('title', 'ilike', $like)
                ->orWhere('reference_no', 'ilike', $like)
                ->orWhereRaw('cast(amount as text) ilike ?', [$like])
                ->orWhereRaw('cast(legacy_id as text) ilike ?', [$like])
                ->orWhereHas('vendor', fn ($v) => $v->where('name', 'ilike', $like))
                ->orWhereHas('method', fn ($m) => $m->where('name', 'ilike', $like)->orWhere('name_en', 'ilike', $like)));
        }

        match ($filters['sort']) {
            'old' => $query->orderByRaw('published_on asc nulls last'),
            'title' => $query->orderBy('title'),
            'high' => $query->orderByRaw('amount desc nulls last'),
            'low' => $query->orderByRaw('amount asc nulls last'),
            default => $query->orderByRaw('published_on desc nulls last'),
        };
        $records = $query->orderByDesc('fiscal_year')->orderByDesc('id')->paginate($filters['size'])->withQueryString();

        return view('web.procurement.index', [
            'filters' => $filters,
            'records' => $records,
            'total' => ProcurementRecord::published()->count(),
            'counts' => ProcurementRecord::published()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type'),
            'years' => ProcurementRecord::published()->distinct()->orderByDesc('fiscal_year')->pluck('fiscal_year'),
            'methods' => ProcurementMethod::whereHas('records', fn ($q) => $q->published())->orderBy('name')->get(),
            'categories' => self::CATEGORIES,
        ]);
    }
}
