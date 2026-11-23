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
        'type', 'fiscal_year', 'title', 'vendor_name', 'vendor_tax_id', 'agency_id', 'work_type_id', 'method_id',
        'amount', 'published_on', 'reference_no', 'reason', 'status', 'legacy_id', 'legacy_user', 'created_by', 'updated_by',
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
