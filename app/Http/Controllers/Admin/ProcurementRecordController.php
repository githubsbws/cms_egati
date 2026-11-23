<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProcurementRecordRequest;
use App\Models\ProcurementAgency;
use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use App\Models\ProcurementVendor;
use App\Models\ProcurementWorkType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Procurement records of one type (plan, price, notice, winner_in, winner_out). The type comes from the route,
 * so one controller serves the five menu pages.
 */
class ProcurementRecordController extends Controller
{
    public function index(Request $request, string $type): View
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'agency' => ['nullable', 'integer'],
            'method' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:published,draft'],
        ]);

        $records = ProcurementRecord::query()
            ->where('type', $type)
            ->with(['agency', 'method', 'workType', 'vendor', 'attachments'])
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('fiscal_year', $year))
            ->when($filters['agency'] ?? null, fn ($query, $id) => $query->where('agency_id', $id))
            ->when($filters['method'] ?? null, fn ($query, $id) => $query->where('method_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($q) => $q->where('title', 'ilike', $like)
                    ->orWhere('reference_no', 'ilike', $like)
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'ilike', $like)->orWhere('tax_id', 'ilike', $like)));
            })
            ->orderByDesc('fiscal_year')
            ->orderByDesc('published_on')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.procurement.records.index', [
            'type' => $type,
            'records' => $records,
            'years' => ProcurementRecord::where('type', $type)->distinct()->orderByDesc('fiscal_year')->pluck('fiscal_year'),
            'agencies' => ProcurementAgency::orderBy('name')->get(),
            'methods' => ProcurementMethod::orderBy('name')->get(),
            'fields' => ProcurementRecord::FIELDS[$type],
        ]);
    }

    public function create(string $type): View
    {
        $record = new ProcurementRecord(['type' => $type, 'status' => 'published', 'fiscal_year' => (int) now()->format('Y'), 'published_on' => now()]);

        return $this->form($type, $record);
    }

    public function store(SaveProcurementRecordRequest $request, string $type): RedirectResponse
    {
        DB::transaction(fn () => $this->save($request, new ProcurementRecord(['type' => $type]), $type));

        return redirect()->route("admin.procurement.{$type}.index")->with('success', __('admin/procurement.saved'));
    }

    public function edit(ProcurementRecord $record, string $type): View
    {
        abort_unless($record->type === $type, 404);

        return $this->form($type, $record->load(['vendor', 'attachments']));
    }

    public function update(SaveProcurementRecordRequest $request, ProcurementRecord $record, string $type): RedirectResponse
    {
        abort_unless($record->type === $type, 404);
        DB::transaction(fn () => $this->save($request, $record, $type));

        return redirect()->route("admin.procurement.{$type}.index")->with('success', __('admin/procurement.saved'));
    }

    /**
     * Soft delete: removed from the website, row and files kept.
     */
    public function destroy(ProcurementRecord $record, string $type): RedirectResponse
    {
        abort_unless($record->type === $type, 404);
        $record->delete();

        return redirect()->route("admin.procurement.{$type}.index")->with('success', __('admin/procurement.deleted'));
    }

    protected function form(string $type, ProcurementRecord $record): View
    {
        return view('admin.procurement.records.form', [
            'type' => $type,
            'record' => $record,
            'fields' => ProcurementRecord::FIELDS[$type],
            'kinds' => ProcurementRecord::ATTACHMENT_KINDS[$type],
            'agencies' => ProcurementAgency::where(fn ($q) => $q->active()->orWhere('id', $record->agency_id))->orderBy('name')->get(),
            'workTypes' => ProcurementWorkType::where(fn ($q) => $q->active()->orWhere('id', $record->work_type_id))->orderBy('name')->get(),
            'methods' => ProcurementMethod::where(fn ($q) => $q->active()->orWhere('id', $record->method_id))->orderBy('name')->get(),
            'vendorNames' => ProcurementVendor::orderBy('name')->pluck('name')->unique()->values(),
        ]);
    }

    protected function save(SaveProcurementRecordRequest $request, ProcurementRecord $record, string $type): void
    {
        $data = $request->validated();
        $record->fill([
            'fiscal_year' => $data['fiscal_year'],
            'title' => trim((string) preg_replace('/\s+/u', ' ', $data['title'])),
            'status' => $data['status'],
            'agency_id' => $data['agency_id'] ?? null,
            'work_type_id' => $data['work_type_id'] ?? null,
            'method_id' => $data['method_id'] ?? null,
            'amount' => ($data['amount'] ?? '') === '' ? null : $data['amount'],
            'published_on' => $data['published_on'] ?? null,
            'reference_no' => $this->text($data['reference_no'] ?? null),
            'reason' => $this->text($data['reason'] ?? null),
            'updated_by' => $request->user()->id,
        ]);
        if (! $record->exists) {
            $record->created_by = $request->user()->id;
        }
        $record->vendor_id = isset($data['vendor_name'])
            ? ProcurementVendor::findOrCreateByName($data['vendor_name'], $data['vendor_tax_id'] ?? null)->id
            : null;
        $record->save();

        $this->saveAttachments($request, $record, $type);
    }

    /**
     * One file or link per slot (kind). A new upload or link replaces the old one; the tick box removes it.
     */
    protected function saveAttachments(SaveProcurementRecordRequest $request, ProcurementRecord $record, string $type): void
    {
        foreach (ProcurementRecord::ATTACHMENT_KINDS[$type] as $order => $kind) {
            $current = $record->attachments()->where('kind', $kind)->first();
            $file = $request->file("files.{$kind}");
            $url = $this->text($request->input("urls.{$kind}"));

            if ($file || $url || $request->boolean("remove.{$kind}")) {
                if ($current && ($file || $request->boolean("remove.{$kind}") || $url !== $current->external_url)) {
                    UploadHelper::delete($current->file_path);
                    $current->delete();
                    $current = null;
                }
                if ($file) {
                    $record->attachments()->create(['kind' => $kind, 'file_path' => UploadHelper::store($file, "procurement/{$type}"), 'sort_order' => $order]);
                } elseif ($url && ! $request->boolean("remove.{$kind}") && ! $current) {
                    $record->attachments()->create(['kind' => $kind, 'external_url' => $url, 'sort_order' => $order]);
                }
            }
        }
    }

    private function text(?string $value): ?string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', (string) $value));

        return $value === '' ? null : $value;
    }
}
