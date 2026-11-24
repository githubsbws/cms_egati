<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementVendor extends Model
{
    protected $fillable = ['name', 'tax_id'];

    /**
     * @return HasMany<ProcurementRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(ProcurementRecord::class, 'vendor_id');
    }

    /**
     * Find the vendor with this name and tax id (names compared without case and extra spaces), or create it.
     */
    public static function findOrCreateByName(string $name, ?string $taxId): self
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        $taxId = trim((string) preg_replace('/\s+/', '', (string) $taxId)) ?: null;

        $found = static::whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($taxId, fn ($q) => $q->where('tax_id', $taxId), fn ($q) => $q->whereNull('tax_id'))
            ->first();

        return $found ?? static::create(['name' => $name, 'tax_id' => $taxId]);
    }
}
