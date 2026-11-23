<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Choice lists (agencies, work categories, methods) are switched off instead of deleted once used:
     * is_active 'y' / 'n', like the other setting tables. Old records keep pointing at an inactive choice.
     */
    public function up(): void
    {
        foreach (['procurement_agencies', 'procurement_work_types', 'procurement_methods'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->char('is_active', 1)->default('y');
            });
        }
    }

    public function down(): void
    {
        foreach (['procurement_agencies', 'procurement_work_types', 'procurement_methods'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
