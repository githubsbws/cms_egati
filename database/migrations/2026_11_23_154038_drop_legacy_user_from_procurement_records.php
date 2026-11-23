<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The old system's user name was kept only for reference and nothing reads it.
     * (legacy_id stays: it links a record to its row in the old database.)
     */
    public function up(): void
    {
        Schema::table('procurement_records', function (Blueprint $table) {
            $table->dropColumn('legacy_user');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_records', function (Blueprint $table) {
            $table->string('legacy_user')->nullable();
        });
    }
};
