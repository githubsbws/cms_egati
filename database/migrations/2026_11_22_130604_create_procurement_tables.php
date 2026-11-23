<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Procurement (จัดื้อจัดจ้าง). The customer's old database had 5 content tables
     * (plan, middle price, announcement, winner, winner of small purchases) plus per-year
     * and per-user log tables. Here they are one record table with a "type", and the
     * attached files are a child table.
     *
     * type: plan | price | notice | winner_in | winner_out  (same keys as the design draft)
     */
    public function up(): void
    {
        Schema::create('procurement_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('procurement_work_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('procurement_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('procurement_records', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedSmallInteger('fiscal_year');
            $table->string('title', 500);
            $table->string('vendor_name')->nullable();
            $table->string('vendor_tax_id', 50)->nullable();
            $table->foreignId('agency_id')->nullable()->constrained('procurement_agencies')->nullOnDelete();
            $table->foreignId('work_type_id')->nullable()->constrained('procurement_work_types')->nullOnDelete();
            $table->foreignId('method_id')->nullable()->constrained('procurement_methods')->nullOnDelete();
            $table->decimal('amount', 16, 2)->nullable();
            $table->date('published_on')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('reason')->nullable();
            $table->string('status', 20)->default('published');
            $table->unsignedInteger('legacy_id')->nullable();
            $table->string('legacy_user')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'legacy_id']);
            $table->index(['type', 'fiscal_year', 'published_on']);
            $table->index(['status', 'type']);
        });

        Schema::create('procurement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('procurement_records')->cascadeOnDelete();
            // attachment | middle_price | draft_announcement | announcement | link
            $table->string('kind', 30)->default('attachment');
            // Uploaded file (public disk) or, for old data, the address on the old website.
            $table->string('file_path', 500)->nullable();
            $table->string('external_url', 1000)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_attachments');
        Schema::dropIfExists('procurement_records');
        Schema::dropIfExists('procurement_methods');
        Schema::dropIfExists('procurement_work_types');
        Schema::dropIfExists('procurement_agencies');
    }
};
