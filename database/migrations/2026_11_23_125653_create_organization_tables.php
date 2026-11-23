<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Organization (admin menu 15): board of directors + committees, management team, company structure chart.
     *
     * org_groups: code (board, management, audit ...), type = board | management | committee.
     * org_members: people in a group. row_no is the line they appear on (1 = top) in the website layout.
     * org_charts: one structure chart image per language.
     */
    public function up(): void
    {
        Schema::create('org_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('type', 20);
            $table->string('document_path', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('org_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('org_groups')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->foreign('locale')->references('code')->on('languages')->cascadeOnUpdate();
            $table->string('name');
            $table->unique(['group_id', 'locale']);
        });

        Schema::create('org_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('org_groups')->cascadeOnDelete();
            $table->unsignedSmallInteger('row_no')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('photo_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'row_no', 'sort_order']);
        });

        Schema::create('org_member_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('org_members')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->foreign('locale')->references('code')->on('languages')->cascadeOnUpdate();
            $table->string('name');
            $table->string('role')->nullable();
            $table->unique(['member_id', 'locale']);
        });

        Schema::create('org_charts', function (Blueprint $table) {
            $table->string('locale', 10)->primary();
            $table->foreign('locale')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_charts');
        Schema::dropIfExists('org_member_translations');
        Schema::dropIfExists('org_members');
        Schema::dropIfExists('org_group_translations');
        Schema::dropIfExists('org_groups');
    }
};
