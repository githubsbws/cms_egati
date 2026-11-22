<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->foreign('locale')->references('code')->on('languages')->cascadeOnUpdate();
            $table->string('title', 500);
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->text('content')->nullable();
            $table->unique(['post_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
