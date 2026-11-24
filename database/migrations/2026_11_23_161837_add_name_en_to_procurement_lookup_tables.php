<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional English name of a choice, shown on the English website (the Thai name is used when empty).
     * The English names of the procurement methods come from the design draft.
     */
    public function up(): void
    {
        foreach (['procurement_agencies', 'procurement_work_types', 'procurement_methods'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
            });
        }

        $file = database_path('seeders/data/procurement_method_names.json');
        if (is_file($file)) {
            foreach (json_decode(file_get_contents($file), true) as $thai => $english) {
                DB::table('procurement_methods')->where('name', $thai)->update(['name_en' => $english]);
            }
        }
    }

    public function down(): void
    {
        foreach (['procurement_agencies', 'procurement_work_types', 'procurement_methods'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('name_en');
            });
        }
    }
};
