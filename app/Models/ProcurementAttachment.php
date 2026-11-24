<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementAttachment extends Model
{
    protected $fillable = ['record_id', 'kind', 'file_path', 'external_url', 'sort_order'];

    /**
     * @return BelongsTo<ProcurementRecord, $this>
     */
    public function record(): BelongsTo
    {
        return $this->belongsTo(ProcurementRecord::class, 'record_id');
    }

    /**
     * Where to open the file: an uploaded file first, else the old website address.
     */
    public function getUrlAttribute(): ?string
    {
        return web_file_url($this->file_path) ?? $this->external_url;
    }
}
