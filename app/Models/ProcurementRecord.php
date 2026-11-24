<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRecord extends Model
{
    use SoftDeletes;

    public const TYPES = ['plan', 'price', 'notice', 'winner_in', 'winner_out'];

    protected $fillable = [
        'type', 'fiscal_year', 'title', 'vendor_id', 'agency_id', 'work_type_id', 'method_id',
        'amount', 'published_on', 'reference_no', 'reason', 'status', 'legacy_id', 'created_by', 'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<ProcurementRecord>  $query
     * @return Builder<ProcurementRecord>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Which optional fields each type uses in the back office form.
     *
     * @var array<string, list<string>>
     */
    public const FIELDS = [
        'plan' => ['amount'],
        'price' => ['agency', 'method', 'date'],
        'notice' => ['agency', 'work_type', 'method', 'date'],
        'winner_in' => ['vendor', 'agency', 'method', 'amount', 'date', 'reference', 'reason'],
        'winner_out' => ['vendor', 'agency', 'method', 'amount', 'date', 'reference', 'reason'],
    ];

    /**
     * Attachment slots of each type: one file or one link per kind.
     *
     * @var array<string, list<string>>
     */
    public const ATTACHMENT_KINDS = [
        'plan' => ['attachment', 'link'],
        'price' => ['attachment'],
        'notice' => ['middle_price', 'draft_announcement', 'announcement'],
        'winner_in' => ['attachment'],
        'winner_out' => ['attachment'],
    ];

    /**
     * @return BelongsTo<ProcurementVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ProcurementVendor::class, 'vendor_id');
    }

    /**
     * @return BelongsTo<ProcurementAgency, $this>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(ProcurementAgency::class, 'agency_id');
    }

    /**
     * @return BelongsTo<ProcurementWorkType, $this>
     */
    public function workType(): BelongsTo
    {
        return $this->belongsTo(ProcurementWorkType::class, 'work_type_id');
    }

    /**
     * @return BelongsTo<ProcurementMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ProcurementMethod::class, 'method_id');
    }

    /**
     * @return HasMany<ProcurementAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ProcurementAttachment::class, 'record_id')->orderBy('sort_order');
    }
}
