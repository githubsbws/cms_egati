<?php

namespace App\Http\Requests\Admin;

use App\Models\ProcurementRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add or edit a procurement record. Which fields apply depends on the type taken from the route
 * (plan, price, notice, winner_in, winner_out); see ProcurementRecord::FIELDS.
 */
class SaveProcurementRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Amounts are typed with commas (1,250,000.00).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge(['amount' => str_replace([',', ' '], '', (string) $this->input('amount'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = (string) $this->route('type');
        $fields = ProcurementRecord::FIELDS[$type] ?? [];
        $uses = fn (string $field): bool => in_array($field, $fields, true);
        $file = ['file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:20480'];

        return [
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2200'],
            'title' => ['required', 'string', 'max:500'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'agency_id' => $uses('agency') ? ['nullable', 'integer', 'exists:procurement_agencies,id'] : ['prohibited'],
            'work_type_id' => $uses('work_type') ? ['nullable', 'integer', 'exists:procurement_work_types,id'] : ['prohibited'],
            'method_id' => $uses('method') ? ['nullable', 'integer', 'exists:procurement_methods,id'] : ['prohibited'],
            'amount' => $uses('amount') ? ['nullable', 'numeric', 'min:0', 'max:9999999999999'] : ['prohibited'],
            'published_on' => $uses('date') ? ['required', 'date'] : ['prohibited'],
            'vendor_name' => $uses('vendor') ? ['required', 'string', 'max:255'] : ['prohibited'],
            'vendor_tax_id' => $uses('vendor') ? ['nullable', 'string', 'max:50'] : ['prohibited'],
            'reference_no' => $uses('reference') ? ['nullable', 'string', 'max:255'] : ['prohibited'],
            'reason' => $uses('reason') ? ['nullable', 'string', 'max:255'] : ['prohibited'],
            'files' => ['nullable', 'array'],
            'files.*' => $file,
            'urls' => ['nullable', 'array'],
            'urls.*' => ['nullable', 'url', 'max:1000'],
            'remove' => ['nullable', 'array'],
            'remove.*' => ['boolean'],
        ];
    }
}
