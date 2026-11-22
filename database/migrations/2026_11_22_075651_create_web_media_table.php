<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Front office images/videos that the back office can replace.
     * No row for a key means the view falls back to the default file in public/web-assets.
     */
    public function up(): void
    {
        Schema::create('web_media', function (Blueprint $table) {
            $table->string('key', 150)->primary();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_media');
    }
};
