<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcurementAgency;
use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use App\Models\ProcurementWorkType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The three choice lists used by procurement records, one menu page each: agencies, work categories, methods.
 * A choice is switched off (is_active = 'n') instead of deleted once records use it; only an unused one can be deleted.
 * The list comes from the route default "kind" (agency | work_type | method).
 */
class ProcurementLookupController extends Controller
{
    /** @var array<string, array{0: class-string, 1: string}> kind => [model, column on procurement_records] */
    private const KINDS = [
        'agency' => [ProcurementAgency::class, 'agency_id'],
        'work_type' => [ProcurementWorkType::class, 'work_type_id'],
        'method' => [ProcurementMethod::class, 'method_id'],
    ];

    public function index(string $kind): View
    {
        [$model] = $this->kind($kind);

        return view('admin.procurement.lookups', [
            'kind' => $kind,
            'items' => $model::query()
                ->withCount(['records as records_count' => fn ($q) => $q->withoutTrashed()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, string $kind): RedirectResponse
    {
        [$model] = $this->kind($kind);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique((new $model)->getTable(), 'name')],
            'name_en' => ['nullable', 'string', 'max:255'],
        ]);

        $model::create(['name' => $this->clean($data['name']), 'name_en' => $this->clean($data['name_en'] ?? '') ?: null, 'is_active' => 'y']);

        return redirect()->route("admin.procurement.{$kind}.index")->with('success', __('admin/procurement.saved'));
    }

    public function update(Request $request, int $id, string $kind): RedirectResponse
    {
        [$model] = $this->kind($kind);
        $item = $model::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($item->getTable(), 'name')->ignore($item->id)],
            'name_en' => ['nullable', 'string', 'max:255'],
        ]);

        $item->update(['name' => $this->clean($data['name']), 'name_en' => $this->clean($data['name_en'] ?? '') ?: null, 'is_active' => $request->boolean('is_active') ? 'y' : 'n']);

        return redirect()->route("admin.procurement.{$kind}.index")->with('success', __('admin/procurement.saved'));
    }

    /**
     * An item that records use cannot be deleted (switch it off instead).
     */
    public function destroy(int $id, string $kind): RedirectResponse
    {
        [$model, $column] = $this->kind($kind);
        $item = $model::findOrFail($id);

        if (ProcurementRecord::withTrashed()->where($column, $item->id)->exists()) {
            return redirect()->route("admin.procurement.{$kind}.index")->withErrors(__('admin/procurement.lookup_in_use'));
        }
        $item->delete();

        return redirect()->route("admin.procurement.{$kind}.index")->with('success', __('admin/procurement.deleted'));
    }

    /**
     * @return array{0: class-string, 1: string}
     */
    private function kind(string $kind): array
    {
        return self::KINDS[$kind] ?? abort(404);
    }

    private function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
