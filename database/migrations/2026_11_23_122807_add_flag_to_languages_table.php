<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The flag shown in the website language switcher now lives on the language row
     * (a path under web-assets/ or an uploaded file), instead of being fixed in the Blade file.
     */
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('flag')->nullable()->after('direction');
        });

        DB::table('languages')->where('code', 'th')->update(['flag' => 'web-assets/flags/th.svg']);
        DB::table('languages')->where('code', 'en')->update(['flag' => 'web-assets/flags/gb.svg']);
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('flag');
        });
    }
};
