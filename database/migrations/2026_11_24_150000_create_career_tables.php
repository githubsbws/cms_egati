<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Job families (legal, audit, ...). Switched off instead of deleted once a job uses them.
        Schema::create('career_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // One recruitment notice. The status shown on the website (open, upcoming, closed) comes from the dates and the stage.
        Schema::create('career_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->foreignId('category_id')->constrained('career_categories');
            $table->unsignedSmallInteger('vacancies')->default(1);
            $table->date('start_on');
            $table->date('deadline_on');
            $table->date('updated_on');
            $table->string('stage', 20)->default('application');
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('career_job_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('career_jobs')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('department')->nullable();
            $table->jsonb('qualifications')->nullable();
            $table->timestamps();

            $table->foreign('locale')->references('code')->on('languages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['job_id', 'locale']);
        });

        // Documents of a notice: announcement, forms, candidate lists, results. Labels are a locale => text map.
        Schema::create('career_job_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('career_jobs')->cascadeOnDelete();
            $table->jsonb('labels');
            $table->string('file_path')->nullable();
            $table->string('external_url', 1000)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->boolean('is_notice')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $this->seed();
    }

    public function down(): void
    {
        Schema::dropIfExists('career_job_documents');
        Schema::dropIfExists('career_job_translations');
        Schema::dropIfExists('career_jobs');
        Schema::dropIfExists('career_categories');
    }

    /**
     * The ten notices and six job families of the website design.
     */
    private function seed(): void
    {
        $data = json_decode((string) file_get_contents(database_path('seeders/data/careers.json')), true);
        $locales = DB::table('languages')->pluck('code')->all();
        $now = now();

        $categoryIds = [];
        foreach ($data['categories'] as $category) {
            $categoryIds[$category['code']] = DB::table('career_categories')->insertGetId([
                'name' => $category['name'],
                'name_en' => $category['name_en'],
                'sort_order' => $category['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($data['jobs'] as $job) {
            $jobId = DB::table('career_jobs')->insertGetId([
                'slug' => $job['slug'],
                'category_id' => $categoryIds[$job['category']],
                'vacancies' => $job['vacancies'],
                'start_on' => $job['start'],
                'deadline_on' => $job['deadline'],
                'updated_on' => $job['updated'],
                'stage' => $job['stage'],
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($job['translations'] as $locale => $translation) {
                if (! in_array($locale, $locales, true)) {
                    continue;
                }
                DB::table('career_job_translations')->insert([
                    'job_id' => $jobId,
                    'locale' => $locale,
                    'title' => $translation['title'],
                    'department' => $translation['department'],
                    'qualifications' => json_encode($translation['qualifications'], JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($job['documents'] as $order => $document) {
                DB::table('career_job_documents')->insert([
                    'job_id' => $jobId,
                    'labels' => json_encode($document['labels'], JSON_UNESCAPED_UNICODE),
                    'file_path' => $document['file'],
                    'external_url' => $document['url'],
                    'size_bytes' => $document['size'],
                    'is_notice' => $document['is_notice'],
                    'sort_order' => $order + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
