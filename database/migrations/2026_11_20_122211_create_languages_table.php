<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->string('code', 12)->primary();
            $table->string('name', 80);
            $table->string('native_name', 80);
            $table->char('direction', 3)->default('ltr');
            $table->char('is_default', 1)->default('n');
            $table->char('is_active', 1)->default('y');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        // Only one row may have is_default = y at a time.
        DB::statement("CREATE UNIQUE INDEX languages_one_default ON languages (is_default) WHERE is_default = 'y'");

        DB::table('languages')->insert([
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'direction' => 'ltr', 'is_default' => 'y', 'is_active' => 'y', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_default' => 'n', 'is_active' => 'y', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
