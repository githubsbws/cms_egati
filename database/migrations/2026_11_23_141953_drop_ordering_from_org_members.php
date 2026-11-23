<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The customer says everyone on the board / management team is equal, so people have no
     * ranking fields. They are listed in the order they were added (the import kept the old order).
     */
    public function up(): void
    {
        Schema::table('org_members', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'row_no', 'sort_order']);
            $table->dropColumn(['row_no', 'sort_order']);
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('org_members', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->unsignedSmallInteger('row_no')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index(['group_id', 'row_no', 'sort_order']);
        });
    }
};
