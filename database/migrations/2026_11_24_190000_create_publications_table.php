<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Credit ratings, financial statements and annual reports. The year is stored as a Gregorian year (the website shows B.E. in Thai).
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedSmallInteger('year');
            $table->string('period', 10)->nullable();
            $table->string('agency', 100)->nullable();
            $table->string('rating', 20)->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url', 1000)->nullable();
            $table->string('file_path_en')->nullable();
            $table->string('external_url_en', 1000)->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'year']);
        });

        $data = json_decode((string) file_get_contents(database_path('seeders/data/publications.json')), true);
        $now = now();
        foreach ($data['items'] as $item) {
            DB::table('publications')->insert([
                'type' => $item['type'],
                'year' => $item['year'],
                'period' => $item['period'] ?? null,
                'agency' => $item['agency'] ?? null,
                'rating' => $item['rating'] ?? null,
                'external_url' => $item['url'],
                'external_url_en' => $item['url_en'] ?? null,
                'cover_image' => $item['cover'] ?? null,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
