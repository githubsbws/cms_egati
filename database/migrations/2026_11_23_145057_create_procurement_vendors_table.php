<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vendors (winners) become their own table so the same company is stored once, not typed again
     * on every record. The old name/tax id columns of procurement_records are moved here, then dropped.
     * Names are tidied while moving (extra spaces removed); the same name + tax id becomes one vendor.
     */
    public function up(): void
    {
        Schema::create('procurement_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_id', 50)->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('tax_id');
        });

        Schema::table('procurement_records', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('title')->constrained('procurement_vendors')->nullOnDelete();
        });

        $vendors = [];
        DB::table('procurement_records')->whereNotNull('vendor_name')->orderBy('id')->each(function (object $record) use (&$vendors): void {
            $name = trim((string) preg_replace('/\s+/u', ' ', $record->vendor_name));
            $tax = trim((string) preg_replace('/\s+/', '', (string) $record->vendor_tax_id)) ?: null;
            if ($name === '') {
                return;
            }
            $key = mb_strtolower($name).'|'.$tax;
            $vendors[$key] ??= DB::table('procurement_vendors')->insertGetId(['name' => $name, 'tax_id' => $tax, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('procurement_records')->where('id', $record->id)->update(['vendor_id' => $vendors[$key]]);
        });

        Schema::table('procurement_records', function (Blueprint $table) {
            $table->dropColumn(['vendor_name', 'vendor_tax_id']);
        });
    }

    public function down(): void
    {
        Schema::table('procurement_records', function (Blueprint $table) {
            $table->string('vendor_name')->nullable();
            $table->string('vendor_tax_id', 50)->nullable();
        });

        DB::statement('update procurement_records r set vendor_name = v.name, vendor_tax_id = v.tax_id from procurement_vendors v where v.id = r.vendor_id');

        Schema::table('procurement_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
        Schema::dropIfExists('procurement_vendors');
    }
};
